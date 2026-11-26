<?php
declare(strict_types=1);

function shop_e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function shop_url(string $page = 'home', array $params = []): string
{
    if ($page !== 'home') {
        $params = ['page' => $page] + $params;
    }

    return 'index.php' . ($params === [] ? '' : '?' . http_build_query($params));
}

function shop_flash(string $type, string $message): void
{
    $_SESSION['shop_flash'] = ['type' => $type, 'message' => $message];
}

function shop_take_flash(): ?array
{
    $flash = $_SESSION['shop_flash'] ?? null;
    unset($_SESSION['shop_flash']);

    return is_array($flash) ? $flash : null;
}

function shop_categories(PDO $pdo): array
{
    return $pdo->query(
        'SELECT id, slug, name, description FROM shop_categories ORDER BY id'
    )->fetchAll();
}

function shop_products(PDO $pdo, string $search = '', ?string $category = null, string $sort = 'featured', bool $unionLab = false): array
{
    $orderBy = [
        'featured' => 'p.is_featured DESC, p.id ASC',
        'price-low' => 'p.price ASC, p.id ASC',
        'price-high' => 'p.price DESC, p.id ASC',
        'name' => 'p.name ASC, p.id ASC',
    ][$sort] ?? 'p.is_featured DESC, p.id ASC';

    // Lab 03 deliberately makes the MAIN storefront search query vulnerable.
    // This branch runs against the same MySQL shop database and returns the
    // same product-shaped columns so UNION rows render in the normal product grid.
    if ($unionLab) {
        if ($search === '') {
            return [];
        }
        $sql = "SELECT p.id, p.slug, p.name, p.product_code, p.description, p.price,
                       p.stock, p.image_class, p.is_featured, c.name AS category_name,
                       c.slug AS category_slug
                FROM shop_products AS p
                JOIN shop_categories AS c ON c.id = p.category_id
                WHERE p.name LIKE '%" . $search . "%'";
        $statement = $pdo->query($sql);
        return $statement->fetchAll();
    }

    $where = ['1 = 1'];
    $params = [];
    if ($search !== '') {
        $where[] = '(p.name LIKE :search_name OR p.description LIKE :search_description OR p.product_code LIKE :search_code)';
        $term = '%' . $search . '%';
        $params['search_name'] = $term;
        $params['search_description'] = $term;
        $params['search_code'] = $term;
    }
    if ($category !== null && $category !== '') {
        $where[] = 'c.slug = :category';
        $params['category'] = $category;
    }

    $statement = $pdo->prepare(
        'SELECT p.id, p.slug, p.name, p.product_code, p.description, p.price,
                p.stock, p.image_class, p.is_featured, c.name AS category_name,
                c.slug AS category_slug
         FROM shop_products AS p
         JOIN shop_categories AS c ON c.id = p.category_id
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY ' . $orderBy
    );
    $statement->execute($params);

    return $statement->fetchAll();
}

function shop_product(PDO $pdo, string $slug): ?array
{
    $statement = $pdo->prepare(
        'SELECT p.id, p.slug, p.name, p.product_code, p.description, p.price,
                p.stock, p.image_class, c.name AS category_name, c.slug AS category_slug
         FROM shop_products AS p
         JOIN shop_categories AS c ON c.id = p.category_id
         WHERE p.slug = :slug
         LIMIT 1'
    );
    $statement->execute(['slug' => $slug]);
    $product = $statement->fetch();

    return $product === false ? null : $product;
}

function shop_cart_items(PDO $pdo): array
{
    $cart = $_SESSION['shop_cart'] ?? [];
    if (!is_array($cart) || $cart === []) {
        return [];
    }

    $cart = array_filter($cart, static fn ($quantity, $id): bool =>
        filter_var($id, FILTER_VALIDATE_INT) !== false &&
        filter_var($quantity, FILTER_VALIDATE_INT) !== false &&
        (int) $quantity > 0,
        ARRAY_FILTER_USE_BOTH
    );
    if ($cart === []) {
        $_SESSION['shop_cart'] = [];
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = [];
    $params = [];
    foreach ($ids as $index => $id) {
        $key = 'product_' . $index;
        $placeholders[] = ':' . $key;
        $params[$key] = $id;
    }

    $statement = $pdo->prepare(
        'SELECT p.id, p.slug, p.name, p.product_code, p.price, p.stock, p.image_class
         FROM shop_products AS p
         WHERE p.id IN (' . implode(', ', $placeholders) . ')'
    );
    $statement->execute($params);
    $items = [];
    foreach ($statement->fetchAll() as $product) {
        $quantity = min((int) $cart[$product['id']], (int) $product['stock'], 99);
        if ($quantity < 1) {
            unset($_SESSION['shop_cart'][$product['id']]);
            continue;
        }
        $product['quantity'] = $quantity;
        $product['line_total'] = (float) $product['price'] * $quantity;
        $items[] = $product;
    }

    return $items;
}

function shop_cart_count(): int
{
    $cart = $_SESSION['shop_cart'] ?? [];
    if (!is_array($cart)) {
        return 0;
    }

    return array_sum(array_map('intval', $cart));
}

function shop_cart_subtotal(array $items): float
{
    return array_reduce(
        $items,
        static fn (float $total, array $item): float => $total + (float) $item['line_total'],
        0.0
    );
}

function shop_order_is_in_session(int $orderId): bool
{
    $orders = $_SESSION['shop_orders'] ?? [];
    return is_array($orders) && in_array($orderId, array_map('intval', $orders), true);
}
