<?php
declare(strict_types=1);

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        escape_html(csrf_token()) . '">';
}

function require_valid_csrf(): void
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submittedToken) || !is_string($sessionToken) ||
        $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        http_response_code(400);
        exit('Invalid request token. Reload the page and try again.');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function safe_challenge_url(?string $route): ?string
{
    if ($route === null || $route === '' ||
        preg_match('/[\x00-\x20\x7f\\\\]/', $route) === 1) {
        return null;
    }

    if (str_starts_with($route, '/')) {
        return str_starts_with($route, '//') ? null : $route;
    }

    $parts = parse_url($route);
    if ($parts === false || !isset($parts['scheme'], $parts['host']) ||
        $parts['host'] === '' || isset($parts['user']) || isset($parts['pass']) ||
        !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        return null;
    }

    return $route;
}
