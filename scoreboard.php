<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/includes/leaderboard.php';

$user = require_operator();
$connection = database();
$flash = take_flash();
$message = '';
$messageType = '';

// Detect whether migration 006 has been applied (adds expected_flag_hash_algorithm).
$_algoColCheck = $connection->query(
  "SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME   = 'labs'
     AND COLUMN_NAME  = 'expected_flag_hash_algorithm'"
);
$algoColExists = ($_algoColCheck !== false && (int) $_algoColCheck->fetchColumn() > 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_valid_csrf();
  $submittedFlag = $_POST['flag'] ?? '';

  if (!is_string($submittedFlag) || trim($submittedFlag) === '' || strlen(trim($submittedFlag)) > 512) {
    $message = 'ERROR: INVALID_FLAG_FORMAT';
    $messageType = 'is-error';
  } else {
    $submittedFlag = trim($submittedFlag);
    $connection->beginTransaction();

    $operatorLock = $connection->prepare('SELECT id FROM operators WHERE id = :id FOR UPDATE');
    $operatorLock->execute(['id' => $user['id']]);
    if ($operatorLock->fetchColumn() === false) {
      $connection->rollBack();
      unset($_SESSION['operator']);
      session_regenerate_id(true);
      set_flash('error', 'Your operator account is no longer available. Please log in again.');
      header('Location: login.php');
      exit;
    }

    $attemptCount = $connection->prepare(
      'SELECT COUNT(*) FROM flag_submissions
       WHERE operator_id = :operator_id
         AND submitted_at >= CURRENT_TIMESTAMP - INTERVAL 10 MINUTE'
    );
    $attemptCount->execute(['operator_id' => $user['id']]);

    if ((int) $attemptCount->fetchColumn() >= 10) {
      $connection->rollBack();
      $message = 'ERROR: TOO_MANY_FLAG_ATTEMPTS. TRY_AGAIN_LATER';
      $messageType = 'is-error';
    } else {
      $submittedHashSha256 = hash('sha256', $submittedFlag);
      $submittedHashMd5    = md5($submittedFlag);

      // Look up all labs whose stored hash matches either algorithm's digest
      // of the submitted flag.  We select both columns (and the algorithm) so
      // we can verify with the correct function below.
      // The expected_flag_hash_algorithm column was added in migration 006.
      // For installations that pre-date that migration the column may not
      // exist; fall back to sha256-only matching in that case.

      $algoSelect = $algoColExists
        ? ", expected_flag_hash_algorithm"
        : ", 'sha256' AS expected_flag_hash_algorithm";

      // Try sha256 match first, then md5.
      $labQuery = $connection->prepare(
        "SELECT id, points, expected_flag_hash{$algoSelect}
         FROM labs
         WHERE expected_flag_hash IN (:hash_sha256, :hash_md5)
         FOR UPDATE"
      );
      $labQuery->execute([
        'hash_sha256' => $submittedHashSha256,
        'hash_md5'    => $submittedHashMd5,
      ]);
      $matchingLabs = $labQuery->fetchAll();

      // Keep only the lab whose hash matches the algorithm it is configured for.
      $matchingLabs = array_filter(
        $matchingLabs,
        static function (array $lab) use ($submittedHashSha256, $submittedHashMd5): bool {
          $algo  = strtolower((string) ($lab['expected_flag_hash_algorithm'] ?? 'sha256'));
          $expected = strtolower((string) $lab['expected_flag_hash']);
          return match ($algo) {
            'md5'    => hash_equals($expected, $submittedHashMd5),
            default  => hash_equals($expected, $submittedHashSha256),
          };
        }
      );
      $matchingLabs = array_values($matchingLabs);

      // Store the hash that was actually used for the matched algorithm,
      // or fall back to the sha256 digest for the submission log.
      $logHash = $submittedHashSha256;

      if (count($matchingLabs) !== 1) {
        $submission = $connection->prepare(
          'INSERT INTO flag_submissions
           (operator_id, lab_id, submitted_flag_hash, is_correct)
           VALUES (:operator_id, NULL, :flag_hash, 0)'
        );
        $submission->execute([
          'operator_id' => $user['id'],
          'flag_hash' => $logHash,
        ]);
        $connection->commit();
        $message = 'ERROR: FLAG_NOT_RECOGNIZED';
        $messageType = 'is-error';
      } else {
        $challenge  = $matchingLabs[0];
        $completionQuery = $connection->prepare(
          'SELECT id FROM lab_completions
           WHERE operator_id = :operator_id AND lab_id = :lab_id'
        );
        $completionQuery->execute([
          'operator_id' => $user['id'],
          'lab_id' => $challenge['id'],
        ]);

        $submission = $connection->prepare(
          'INSERT INTO flag_submissions
           (operator_id, lab_id, submitted_flag_hash, is_correct)
           VALUES (:operator_id, :lab_id, :flag_hash, 1)'
        );
        $submission->execute([
          'operator_id' => $user['id'],
          'lab_id' => $challenge['id'],
          'flag_hash' => $logHash,
        ]);

        if ($completionQuery->fetchColumn() !== false) {
          $connection->commit();
          $message = 'INFO: FLAG_ALREADY_SOLVED';
        } else {
          $completion = $connection->prepare(
            'INSERT INTO lab_completions (operator_id, lab_id, points_awarded)
             VALUES (:operator_id, :lab_id, :points)'
          );
          $completion->execute([
            'operator_id' => $user['id'],
            'lab_id' => $challenge['id'],
            'points' => $challenge['points'],
          ]);
          $connection->commit();
          $message = 'SUCCESS: FLAG_ACCEPTED (+' . (int) $challenge['points'] . ' PTS)';
          $messageType = 'is-success';
        }
      }
    }
  }
}

$leaderboard = load_leaderboard($connection);
$userStanding = find_standing($leaderboard, $user);
if ($userStanding === null) {
  unset($_SESSION['operator']);
  session_regenerate_id(true);
  set_flash('error', 'Your operator account is no longer available. Please log in again.');
  header('Location: login.php');
  exit;
}
$visibleLeaderboard = array_slice($leaderboard, 0, 10);
if ($userStanding['rank'] > 10) {
  $visibleLeaderboard[] = $userStanding;
}

$_algoSelect2 = $algoColExists
  ? 'l.expected_flag_hash_algorithm,'
  : "'sha256' AS expected_flag_hash_algorithm,";

$challengeQuery = $connection->prepare(
  "SELECT l.id, l.title AS name, l.points,
          l.expected_flag_hash,
          {$_algoSelect2}
          c.id AS completion_id
   FROM labs AS l
   LEFT JOIN lab_completions AS c
     ON c.lab_id = l.id AND c.operator_id = :operator_id
   ORDER BY l.id ASC"
);
$challengeQuery->execute(['operator_id' => $user['id']]);
$challengeRows = $challengeQuery->fetchAll();
$totalChallenges = count($challengeRows);

$activePage = 'scoreboard';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#000000">
  <title>BreachLab // Scoreboard</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <?php require __DIR__ . '/includes/header.php'; ?>

  <main class="page-shell scoreboard-page">
    <section class="terminal-panel scoreboard-heading" aria-labelledby="scoreboard-title">
      <p class="prompt">root@breachlab:~# ./scoreboard --flags</p>
      <h1 id="scoreboard-title" class="section-title">&gt; SCOREBOARD</h1>
      <div class="stats-grid">
        <div class="stat-box">
          <span class="stat-label">[ RANK ]</span>
          <strong class="stat-value warning-text">#<?= e($userStanding['rank']) ?></strong>
        </div>
        <div class="stat-box">
          <span class="stat-label">[ FLAGS ]</span>
          <strong class="stat-value"><?= e($userStanding['solved']) ?><span class="muted-text">/<?= e($totalChallenges) ?></span></strong>
        </div>
        <div class="stat-box">
          <span class="stat-label">[ SCORE ]</span>
          <strong class="stat-value"><?= e(number_format($userStanding['score'])) ?></strong>
        </div>
      </div>
    </section>

    <div class="dashboard-grid">
      <section class="terminal-panel leaderboard-panel" aria-labelledby="leaderboard-title">
        <div class="panel-heading">
          <h2 id="leaderboard-title" class="section-title">&gt; TOP_OPERATORS</h2>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th scope="col">RANK</th>
                <th scope="col">HANDLE</th>
                <th scope="col">SOLVED</th>
                <th scope="col">SCORE</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($visibleLeaderboard as $operator): ?>
                <tr class="<?= $operator['id'] === $user['id'] ? 'is-you' : '' ?>">
                  <td>#<?= e($operator['rank']) ?></td>
                  <td><?= e($operator['username']) ?><?= $operator['id'] === $user['id'] ? ' <span class="you-tag">(YOU)</span>' : '' ?></td>
                  <td><?= e($operator['solved']) ?>/<?= e($totalChallenges) ?></td>
                  <td><?= e(number_format($operator['score'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <div class="side-panels">
        <section class="terminal-panel submit-panel" aria-labelledby="submit-title">
          <h2 id="submit-title" class="section-title">&gt; SUBMIT_FLAG</h2>
          <?php if ($message !== ''): ?>
            <p class="submit-message <?= escape_html($messageType) ?>" role="<?= $messageType === 'is-error' ? 'alert' : 'status' ?>"><?= escape_html($message) ?></p>
          <?php endif; ?>
          <form method="post" class="flag-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input id="flag" name="flag" type="text" aria-label="Enter a challenge flag" placeholder="FLAG{...}" maxlength="512" autocomplete="off" spellcheck="false" required>
            <button class="command-button" type="submit">[ SUBMIT ]</button>
          </form>
        </section>

        <section class="terminal-panel challenges-panel" id="challenges" aria-labelledby="challenges-title">
          <h2 id="challenges-title" class="section-title">&gt; CHALLENGE_PROGRESS</h2>
          <ul class="challenge-list">
            <?php foreach ($challengeRows as $challenge): ?>
              <?php $solved = $challenge['completion_id'] !== null; ?>
              <li class="challenge-item<?= $solved ? ' is-solved' : '' ?>">
                <span><span class="challenge-state" aria-label="<?= $solved ? 'Solved' : 'Not yet solved' ?>"><?= $solved ? '[x]' : '[ ]' ?></span> LAB_<?= str_pad((string) $challenge['id'], 2, '0', STR_PAD_LEFT) ?> — <?= e($challenge['name']) ?></span>
                <span class="challenge-points"><?= e($challenge['points']) ?> PTS</span>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      </div>
    </div>
  </main>
</body>
</html>
