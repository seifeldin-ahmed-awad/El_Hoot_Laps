<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

if (current_operator() !== null) {
    header('Location: scoreboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();

    $usernameInput = $_POST['username'] ?? '';
    $username = is_string($usernameInput) ? trim($usernameInput) : '';
    $password = $_POST['password'] ?? '';

    if (!is_string($password) || $username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        $statement = database()->prepare(
            'SELECT id, username, password_hash FROM operators WHERE username = :username LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $operator = $statement->fetch();

        if ($operator === false || !password_verify($password, $operator['password_hash'])) {
            $error = 'The username or password is incorrect.';
        } else {
            session_regenerate_id(true);
            $_SESSION['operator'] = [
                'id' => (int) $operator['id'],
                'username' => $operator['username'],
            ];
            unset($_SESSION['csrf_token']);
            set_flash('success', 'Login successful. Operator session established.');
            header('Location: scoreboard.php');
            exit;
        }
    }
}

$flash = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <title>BreachLab — Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell auth-page">
        <section class="terminal-panel auth-panel" aria-labelledby="login-heading">
            <p class="prompt">root@breachlab:~# ./login</p>
            <p class="eyebrow">[ OPERATOR_AUTHENTICATION ]</p>
            <h1 id="login-heading" class="section-title">[ LOGIN ]</h1>

            <?php if ($flash !== null): ?>
                <p class="status-message <?= $flash['type'] === 'error' ? 'is-error' : 'is-success' ?>" role="status"><?= escape_html($flash['message']) ?></p>
            <?php endif; ?>
            <?php if ($error !== null): ?>
                <p class="status-message is-error" role="alert"><?= escape_html($error) ?></p>
            <?php endif; ?>

            <form method="post" action="login.php" class="auth-form">
                <?= csrf_field() ?>
                <label for="username">Username</label>
                <input id="username" name="username" type="text" autocomplete="username" minlength="3" maxlength="32" required>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <button class="command-button" type="submit">[ LOGIN ]</button>
            </form>
            <p class="auth-links">No operator account? <a href="register.php">[ SIGN_UP ]</a></p>
            <p class="terminal-footer">STATUS: READY <span aria-hidden="true">|</span> SCOPE: VOLTGEAR_LAB_ONLY</p>
        </section>
    </main>
</body>
</html>
