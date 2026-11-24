<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$labId = filter_var($_GET['lab_id'] ?? null, FILTER_VALIDATE_INT);
if ($labId === false || $labId === null || $labId < 1) {
    set_flash('error', 'Select a valid configured lab.');
    header('Location: scoreboard.php');
    exit;
}

$statement = database()->prepare(
    'SELECT challenge_route, expected_flag_hash FROM labs WHERE id = :id LIMIT 1'
);
$statement->execute(['id' => $labId]);
$lab = $statement->fetch();

if ($lab === false ||
    !is_string($lab['expected_flag_hash']) ||
    preg_match('/\A[a-f0-9]{64}\z/i', $lab['expected_flag_hash']) !== 1 ||
    ($challengeUrl = safe_challenge_url($lab['challenge_route'])) === null) {
    set_flash('error', 'This challenge is not configured yet.');
    header('Location: scoreboard.php');
    exit;
}

$operator = current_operator();
if ($operator !== null) {
    database()->prepare(
        'INSERT IGNORE INTO operator_lab_progress (operator_id, lab_id)
         VALUES (:operator_id, :lab_id)'
    )->execute(['operator_id' => $operator['id'], 'lab_id' => $labId]);
}

header('Location: ' . $challengeUrl);
exit;
