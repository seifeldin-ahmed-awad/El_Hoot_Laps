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

    if (!preg_match('/\A[a-zA-Z0-9_]{3,32}\z/', $username)) {
        $error = 'Usernames must be 3–32 characters using letters, numbers, or underscores.';
    } elseif (!is_string($password) || strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Choose a password between 12 and 72 bytes.';
    } else {
        try {
            $statement = database()->prepare(
                'INSERT INTO operators (username, password_hash) VALUES (:username, :password_hash)'
            );
            $statement->execute([
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            session_regenerate_id(true);
            $_SESSION['operator'] = [
                'id' => (int) database()->lastInsertId(),
                'username' => $username,
            ];
            unset($_SESSION['csrf_token']);
            set_flash('success', 'Operator account created. Welcome to BreachLab.');
            header('Location: scoreboard.php');
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $error = 'That username is already registered. Choose another username.';
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
    <title>BreachLab — Sign Up</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <?php require __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell auth-page">
        <section class="terminal-panel auth-panel" aria-labelledby="register-heading">
            <p class="prompt">root@breachlab:~# ./register</p>
            <p class="eyebrow">[ NEW_OPERATOR_REGISTRATION ]</p>
            <h1 id="register-heading" class="section-title">[ SIGN_UP ]</h1>

            <?php if ($flash !== null): ?>
                <p class="status-message <?= $flash['type'] === 'error' ? 'is-error' : 'is-success' ?>" role="status"><?= escape_html($flash['message']) ?></p>
            <?php endif; ?>
            <?php if ($error !== null): ?>
                <p class="status-message is-error" role="alert"><?= escape_html($error) ?></p>
            <?php endif; ?>

            <form method="post" action="register.php" class="auth-form">
                <?= csrf_field() ?>
                <label for="username">Username</label>
                <input id="username" name="username" type="text" autocomplete="username" minlength="3" maxlength="32" pattern="[A-Za-z0-9_]{3,32}" required>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required>
                <p class="submit-hint">Use 12–72 characters. Passwords are stored as secure hashes.</p>
                <button class="command-button" type="submit">[ CREATE_ACCOUNT ]</button>
            </form>
            <p class="auth-links">Already registered? <a href="login.php">[ LOGIN ]</a></p>
            <p class="terminal-footer">STATUS: READY <span aria-hidden="true">|</span> SCOPE: VOLTGEAR_LAB_ONLY</p>
        </section>
    </main>
</body>
</html>
