<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_name('BREACHLABSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
$isShopTarget = str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/target/shop/index.php');
$scriptPolicy = $isShopTarget ? "script-src 'self' 'unsafe-inline'" : "script-src 'self'";
header("Content-Security-Policy: default-src 'self'; style-src 'self'; {$scriptPolicy}; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/authentication.php';
