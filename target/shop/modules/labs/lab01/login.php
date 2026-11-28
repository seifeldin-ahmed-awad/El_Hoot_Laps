<?php
declare(strict_types=1);

/**
 * Lab 01 – Visible Error-Based SQL Injection
 * Shop-side login page used in Step 9 of the solution sequence.
 *
 * The learner extracts the sales_manager password via error-based
 * injection, then authenticates here.  On success the lab flag is
 * displayed.
 *
 * Directory depths from this file (__DIR__ = …/lab01):
 *   dirname(__DIR__, 3)  → target/shop
 *   dirname(__DIR__, 4)  → target
 *   dirname(__DIR__, 5)  → BreachLab root (where app/ lives)
 */

// Resolve the BreachLab root regardless of web-server document-root config.
define('LAB01_SHOP_ROOT', dirname(__DIR__, 3));
define('LAB01_BREACHLAB_ROOT', dirname(__DIR__, 5));

require_once LAB01_BREACHLAB_ROOT . '/app/bootstrap.php';
require_once LAB01_SHOP_ROOT . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tracking.php';

// Issue a TrackingId cookie if one is not already set.
if (!isset($_COOKIE['TrackingId'])) {
    setcookie('TrackingId', 'ogAZZfxtOKUELbuJ', [
        'expires'  => 0,
        'path'     => '/',
        'httponly' => false,   // intentionally accessible – it is the injection point
        'samesite' => 'Lax',
    ]);
    $_COOKIE['TrackingId'] = 'ogAZZfxtOKUELbuJ';
}

// Run the vulnerable tracking query and capture any SQL error.
$trackingError = lab01_process_tracking_cookie();

$loginError   = null;
$loginSuccess = false;
$flagValue    = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $loginError = 'Enter a username and password.';
    } else {
        $labPdo = lab01_pdo();
        // Parameterised – the login form is NOT the vulnerable sink.
        $stmt = $labPdo->prepare(
            'SELECT password FROM users WHERE username = :u LIMIT 1'
        );
        $stmt->execute(['u' => $username]);
        $row = $stmt->fetch();

        if ($row === false || $row['password'] !== $password) {
            $loginError = 'Invalid username or password.';
        } elseif ($username !== 'sales_manager') {
            $loginError = 'Only the sales_manager account completes this challenge.';
        } else {
            $loginSuccess = true;

            // Retrieve the plaintext flag from the lab challenge database.
            $flagStmt = $labPdo->prepare('SELECT flag_value FROM lab01_flag LIMIT 1');
            $flagStmt->execute();
            $flagRow  = $flagStmt->fetch();
            $flagValue = $flagRow !== false ? md5((string) $flagRow['flag_value']) : null;
        }
    }
}

$pdo          = database(); // main BreachLab MySQL connection
$categories   = shop_categories($pdo);
$cartItems    = shop_cart_items($pdo);
$cartSubtotal = shop_cart_subtotal($cartItems);
$flash        = shop_take_flash();
$search       = '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#FAF8F5">
  <title>Staff Login — Kanso Goods</title>
  <link rel="stylesheet" href="<?= shop_e(rtrim(str_replace('\\', '/', substr(LAB01_SHOP_ROOT, strlen($_SERVER['DOCUMENT_ROOT']))), '/') . '/assets/css/shop.css') ?>">
</head>
<body class="shop-body">

  <?php require LAB01_SHOP_ROOT . '/includes/header.php'; ?>

  <main class="shop-main">

    <?php if ($trackingError !== null): ?>
    <div class="lab01-sql-error" role="alert" aria-label="Database error">
      <p class="lab01-sql-error-label">DATABASE ERROR</p>
      <pre class="lab01-sql-error-msg"><?= htmlspecialchars($trackingError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
    </div>
    <?php endif; ?>

    <section class="listing-page lab01-login-page">
      <p class="eyebrow">KANSO GOODS — STAFF ACCESS</p>
      <h1>Staff<br>Login</h1>

      <?php if ($loginError !== null): ?>
        <p class="shop-flash is-error" role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if ($loginSuccess && $flagValue !== null): ?>
        <div class="lab01-complete">
          <p class="eyebrow lab01-solved-eyebrow">✓ CHALLENGE SOLVED</p>
          <p>You have successfully authenticated as <strong>sales_manager</strong>.</p>
          <p>Copy the MD5 hash below and use it for your <a href="/BreachLab/scoreboard.php" class="text-link">BreachLab Scoreboard</a> own hash analysis.</p>
          <div class="lab01-flag-box">
            <span class="lab01-flag-label">FLAG MD5 HASH</span>
            <code class="lab01-flag-value"><?= htmlspecialchars($flagValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code>
          </div>
          <p class="lab01-flag-note">This flag is unique to this challenge instance.</p>
        </div>
      <?php elseif ($loginSuccess): ?>
        <p class="shop-flash is-success" role="status">Authentication successful. Flag unavailable — contact your platform sales_manager.</p>
      <?php else: ?>
        <form method="post" class="lab01-login-form">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          <div class="lab01-field">
            <label for="lab01-username">Username</label>
            <input id="lab01-username" name="username" type="text"
                   autocomplete="username" required maxlength="64"
                   value="<?= htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
          </div>
          <div class="lab01-field">
            <label for="lab01-password">Password</label>
            <input id="lab01-password" name="password" type="password"
                   autocomplete="current-password" required maxlength="64">
          </div>
          <button class="shop-button lab01-login-submit" type="submit">Log in</button>
        </form>
      <?php endif; ?>
    </section>
  </main>

  <?php require LAB01_SHOP_ROOT . '/includes/footer.php'; ?>

</body>
</html>
