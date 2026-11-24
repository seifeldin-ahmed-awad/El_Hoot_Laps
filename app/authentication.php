<?php
declare(strict_types=1);

function current_operator(): ?array
{
    $operator = $_SESSION['operator'] ?? null;
    if (!is_array($operator)) {
        return null;
    }

    $id = filter_var($operator['id'] ?? null, FILTER_VALIDATE_INT);
    $username = $operator['username'] ?? null;
    if ($id === false || $id === null || $id < 1 ||
        !is_string($username) || $username === '') {
        return null;
    }

    return ['id' => (int) $id, 'username' => $username];
}

function require_operator(): array
{
    $operator = current_operator();
    if ($operator === null) {
        set_flash('error', 'Log in to submit flags and save your progress.');
        header('Location: login.php');
        exit;
    }

    return $operator;
}
