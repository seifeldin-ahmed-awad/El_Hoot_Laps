<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/modules/labs/registry.php';
// Keep every challenge on the storefront endpoint. Lab 03 is selected explicitly
// by its link and uses the search bar; TrackingId auto-detection is only for Labs 02/04.
$trackingProbe = (string) ($_COOKIE['TrackingId'] ?? '');
$autoDetectedLab = 1;
// Lab 01's error-based payloads also contain AND/OR. Detect its distinctive
// CAST((SELECT ... FROM users ...) signature before the generic Lab 02 rule.
if (preg_match('/\bCAST\s*\(\s*\(\s*SELECT\b[\s\S]*?\bFROM\s+users\b/i', $trackingProbe) === 1) {
    $autoDetectedLab = 1; // visible error-based SQLi (sales_manager target)
} elseif (preg_match('/pg\s*_\s*sleep\s*\(/i', $trackingProbe) === 1) {
    $autoDetectedLab = 4; // time-based blind SQLi
} elseif (preg_match('/\b(?:AND|OR)\b/i', $trackingProbe) === 1) {
    $autoDetectedLab = 2; // conditional-response blind SQLi
}
$requestedLab = filter_var($_GET['lab'] ?? null, FILTER_VALIDATE_INT);
$activeLab = is_int($requestedLab) && in_array($requestedLab, [1, 2, 3, 4], true)
    ? $requestedLab
    : $autoDetectedLab;
if (!isset($_COOKIE['TrackingId']) && $activeLab !== 3) {
    $seedCookie = $activeLab === 1 ? 'ogAZZfxtOKUELbuJ' : ($activeLab === 4 ? 'x' : 'xyz');
    setcookie('TrackingId', $seedCookie, [
        'expires' => 0, 'path' => '/', 'httponly' => false, 'samesite' => 'Lax',
    ]);
    $_COOKIE['TrackingId'] = $seedCookie;
}
$labResult = null;
if ($activeLab >= 2 && $activeLab !== 3) {
    require_once __DIR__ . '/modules/labs/shared.php';
    $labResult = lab_shop_process($activeLab);
}
if ($activeLab === 3) {
    $labResult = ['lab_id' => 3, 'message' => '', 'error' => '', 'duration' => 0.0, 'rows' => []];
}
require_once __DIR__ . '/modules/labs/lab01/tracking.php';
$lab01TrackingError = $activeLab === 1 ? lab01_process_tracking_cookie() : null;

// PortSwigger-style response: SQL errors return HTTP 404 and show only the database error.
if ($lab01TrackingError !== null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Not Found\n\n" . $lab01TrackingError;
    exit;
}

$pdo = database();
$page = $_GET['page'] ?? 'home';
$allowedPages = ['home', 'products', 'category', 'product', 'cart', 'checkout', 'account', 'order', 'labs', 'info', 'lab01'];
if (!is_string($page) || !in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $page = 'home';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
        if ($productId === false || $productId === null || $productId < 1 ||
            $quantity === false || $quantity < 1 || $quantity > 99) {
            shop_flash('error', 'Choose a valid item quantity.');
        } else {
            $statement = $pdo->prepare('SELECT stock FROM shop_products WHERE id = :id');
            $statement->execute(['id' => $productId]);
            $stock = $statement->fetchColumn();
            if ($stock === false || (int) $stock < 1) {
                shop_flash('error', 'This item is currently unavailable.');
            } else {
                $cart = $_SESSION['shop_cart'] ?? [];
                $current = isset($cart[$productId]) ? (int) $cart[$productId] : 0;
                $_SESSION['shop_cart'][$productId] = min($current + $quantity, (int) $stock, 99);
                shop_flash('success', 'Added to your bag.');
            }
        }
        header('Location: ' . shop_url('cart'));
        exit;
    }

    if ($action === 'update') {
        $quantities = $_POST['quantities'] ?? [];
        if (!is_array($quantities)) {
            shop_flash('error', 'Your bag could not be updated.');
        } else {
            foreach ($quantities as $rawId => $rawQuantity) {
                $productId = filter_var($rawId, FILTER_VALIDATE_INT);
                $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
                if ($productId === false || $productId === null || $productId < 1 ||
                    $quantity === false || $quantity < 0 || $quantity > 99) {
                    continue;
                }
                if ($quantity === 0) {
                    unset($_SESSION['shop_cart'][$productId]);
                    continue;
                }
                $statement = $pdo->prepare('SELECT stock FROM shop_products WHERE id = :id');
                $statement->execute(['id' => $productId]);
                $stock = $statement->fetchColumn();
                if ($stock === false) {
                    unset($_SESSION['shop_cart'][$productId]);
                } else {
                    $_SESSION['shop_cart'][$productId] = min($quantity, (int) $stock, 99);
                }
            }
            shop_flash('success', 'Your bag has been updated.');
        }
        header('Location: ' . shop_url('cart'));
        exit;
    }

    if ($action === 'remove') {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        if ($productId !== false && $productId !== null && $productId > 0) {
            unset($_SESSION['shop_cart'][$productId]);
        }
        header('Location: ' . shop_url('cart'));
        exit;
    }

    if ($action === 'place-order') {
        $cart = $_SESSION['shop_cart'] ?? [];
        if (!is_array($cart) || $cart === []) {
            shop_flash('error', 'Your bag is empty.');
            header('Location: ' . shop_url('cart'));
            exit;
        }

        $pdo->beginTransaction();
        $lockedProducts = [];
        $stockIssue = false;
        foreach ($cart as $rawId => $rawQuantity) {
            $productId = filter_var($rawId, FILTER_VALIDATE_INT);
            $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
            if ($productId === false || $productId === null || $productId < 1 ||
                $quantity === false || $quantity < 1 || $quantity > 99) {
                $stockIssue = true;
                break;
            }
            $statement = $pdo->prepare(
                'SELECT id, name, price, stock FROM shop_products WHERE id = :id FOR UPDATE'
            );
            $statement->execute(['id' => $productId]);
            $product = $statement->fetch();
            if ($product === false || (int) $product['stock'] < $quantity) {
                $stockIssue = true;
                break;
            }
            $product['quantity'] = $quantity;
            $lockedProducts[] = $product;
        }

        if ($stockIssue) {
            $pdo->rollBack();
            shop_flash('error', 'An item changed availability. Review your bag and try again.');
            header('Location: ' . shop_url('cart'));
            exit;
        }

        $subtotal = 0.0;
        foreach ($lockedProducts as $product) {
            $subtotal += (float) $product['price'] * (int) $product['quantity'];
        }
        $order = $pdo->prepare(
            'INSERT INTO shop_orders (order_token, subtotal) VALUES (:token, :subtotal)'
        );
        $order->execute([
            'token' => bin2hex(random_bytes(16)),
            'subtotal' => number_format($subtotal, 2, '.', ''),
        ]);
        $orderId = (int) $pdo->lastInsertId();
        $orderItem = $pdo->prepare(
            'INSERT INTO shop_order_items
             (order_id, product_id, product_name, unit_price, quantity)
             VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity)'
        );
        $stockUpdate = $pdo->prepare(
            'UPDATE shop_products SET stock = stock - :quantity WHERE id = :id'
        );
        foreach ($lockedProducts as $product) {
            $orderItem->execute([
                'order_id' => $orderId,
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'unit_price' => $product['price'],
                'quantity' => $product['quantity'],
            ]);
            $stockUpdate->execute([
                'quantity' => $product['quantity'],
                'id' => $product['id'],
            ]);
        }
        $pdo->commit();
        $previousOrders = $_SESSION['shop_orders'] ?? [];
        $previousOrders = is_array($previousOrders) ? $previousOrders : [];
        $_SESSION['shop_orders'] = array_slice(
            array_merge([(string) $orderId], $previousOrders),
            0,
            20
        );
        $_SESSION['shop_cart'] = [];
        shop_flash('success', 'Your studio order has been placed. No payment was collected.');
        header('Location: ' . shop_url('order', ['id' => $orderId]));
        exit;
    }

    http_response_code(400);
    exit('Unknown shop action.');
}

$categories = shop_categories($pdo);
$cartItems = shop_cart_items($pdo);
$cartSubtotal = shop_cart_subtotal($cartItems);
$flash = shop_take_flash();
$search = $_GET['q'] ?? '';
$search = is_string($search) ? trim(substr($search, 0, 1000)) : '';
$sort = $_GET['sort'] ?? 'featured';
$sort = is_string($sort) ? $sort : 'featured';
$categorySlug = $_GET['category'] ?? '';
$categorySlug = is_string($categorySlug) ? $categorySlug : '';
$productSlug = $_GET['slug'] ?? '';
$productSlug = is_string($productSlug) ? $productSlug : '';
$product = $productSlug === '' ? null : shop_product($pdo, $productSlug);
try {
    $products = shop_products(
        $pdo,
        $search,
        in_array($page, ['category', 'products'], true) && $categorySlug !== '' ? $categorySlug : null,
        $sort,
        true // Intentionally vulnerable search for the local SQLi training storefront.
    );
    if ($activeLab === 3 && $search !== '') {
        $unionRows = array_values(array_filter($products, static fn(array $row): bool => ($row['category_slug'] ?? '') === 'lab03'));
        if ($unionRows !== []) {
            $labResult['message'] = implode("\n", array_map(
                static fn(array $row): string => implode(' | ', [
                    (string) $row['name'], (string) $row['product_code'], (string) $row['description'],
                ]),
                $unionRows
            ));
        }
    }
} catch (Throwable $e) {
    $products = [];
    $labResult['error'] = $e->getMessage();
    http_response_code(404);
}
$selectedCategory = null;
foreach ($categories as $category) {
    if ($category['slug'] === $categorySlug) {
        $selectedCategory = $category;
        break;
    }
}

$orderId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$order = null;
$orderItems = [];
if ($page === 'order' && $orderId !== false && $orderId !== null &&
    $orderId > 0 && shop_order_is_in_session($orderId)) {
    $statement = $pdo->prepare('SELECT id, status, subtotal, created_at FROM shop_orders WHERE id = :id');
    $statement->execute(['id' => $orderId]);
    $order = $statement->fetch() ?: null;
    if ($order !== null) {
        $statement = $pdo->prepare(
            'SELECT product_name, unit_price, quantity
             FROM shop_order_items WHERE order_id = :order_id ORDER BY id'
        );
        $statement->execute(['order_id' => $orderId]);
        $orderItems = $statement->fetchAll();
    }
}

$orderHistory = [];
$sessionOrders = $_SESSION['shop_orders'] ?? [];
if (!is_array($sessionOrders)) {
    $sessionOrders = [];
}
foreach (array_slice($sessionOrders, 0, 20) as $ownedOrderId) {
    $statement = $pdo->prepare(
        'SELECT id, status, subtotal, created_at FROM shop_orders WHERE id = :id'
    );
    $statement->execute(['id' => (int) $ownedOrderId]);
    $historyOrder = $statement->fetch();
    if ($historyOrder !== false) {
        $orderHistory[] = $historyOrder;
    }
}

$labs = shop_lab_registry($pdo);
$title = match ($page) {
    'products' => 'Shop All',
    'category' => $selectedCategory['name'] ?? 'Collection',
    'product' => $product['name'] ?? 'Object',
    'cart' => 'Your Bag',
    'checkout' => 'Checkout',
    'account' => 'Your Account',
    'order' => 'Order Details',
    'labs' => 'Studio Notes',
    'info' => 'Information',
    'lab01' => 'Lab 01 — Error-Based SQL Injection',
    default => 'Kanso Goods',
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#FAF8F5">
  <title><?= $page === 'home' ? 'Kanso Goods' : shop_e($title . ' — Kanso Goods') ?></title>
  <link rel="stylesheet" href="assets/css/shop.css">
  <script src="assets/js/shop.js" defer></script>
</head>
<body class="shop-body">
  <?php require __DIR__ . '/includes/header.php'; ?>
  <main class="shop-main">
    <?php if ($flash !== null): ?>
      <p class="shop-flash <?= $flash['type'] === 'error' ? 'is-error' : 'is-success' ?>" role="status"><?= shop_e($flash['message']) ?></p>
    <?php endif; ?>
    <?php if ($labResult !== null && ($labResult['message'] !== '' || $labResult['error'] !== '' || $activeLab === 4)): ?>
      <section class="shop-flash <?= $labResult['error'] !== '' ? 'is-error' : 'is-success' ?>" id="lab-result" role="status" aria-live="polite">
        <?php if ($labResult['message'] !== ''): ?><pre><?= shop_e($labResult['message']) ?></pre><?php endif; ?>
        <?php if ($labResult['error'] !== ''): ?><pre><?= shop_e($labResult['error']) ?></pre><?php endif; ?>
        <?php if ($activeLab === 4): ?><small>Request time: <?= number_format((float) $labResult['duration'], 2) ?> seconds</small><?php endif; ?>
      </section>
    <?php endif; ?>
    <?php
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
    require is_file($pageFile) ? $pageFile : __DIR__ . '/pages/home.php';
    ?>
  </main>
  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
