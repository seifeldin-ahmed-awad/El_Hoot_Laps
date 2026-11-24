<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/target.php';
require_once __DIR__ . '/includes/leaderboard.php';

$user = current_operator();
$leaderboard = $user === null ? [] : load_leaderboard(database());
$userStanding = find_standing($leaderboard, $user);
$targetLabId = configured_target_lab_id();
$activePage = 'intro';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#000000">
  <title>BreachLab // Welcome</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="auth.css">
  <script src="welcome.js" defer></script>
</head>
<body>
  <?php require __DIR__ . '/includes/header.php'; ?>

  <main class="page-shell">
    <section class="terminal-panel welcome-terminal" aria-labelledby="welcome-title">
      <p class="prompt">root@breachlab:~# ./welcome</p>
      <div class="welcome-copy">
        <p class="eyebrow">[ CONNECTION_ESTABLISHED ]</p>
        <h1 id="welcome-title" class="typing-line">
          <span id="typed-message">Welcome, operator.</span><span class="cursor" aria-hidden="true"></span>
        </h1>
        <p class="intro-note">A controlled lab. A deliberately flawed shop. Your next lesson starts here.</p>
      </div>

      <div class="info-grid">
        <article class="info-box">
          <h2>[ TARGET ]</h2>
          <p>VoltGear is a fake shop with deliberate security holes.</p>
        </article>
        <article class="info-box">
          <h2>[ GOAL ]</h2>
          <p>Explore each flaw, find the hidden flag, and submit it for points.</p>
          <p>For Labs 1 and 2, recover the required value and submit it as the flag.</p>
        </article>
        <article class="info-box">
          <h2 class="warning-text">[ RULE ]</h2>
          <p>Attack only this lab. Never test these techniques on real sites.</p>
        </article>
      </div>

      <div class="action-row">
        <a class="command-button" href="scoreboard.php">[ OPEN_SCOREBOARD ]</a>
        <?php if ($targetLabId !== null): ?>
          <a class="command-button command-button-outline" href="open_challenge.php?lab_id=<?= $targetLabId ?>" target="_blank" rel="noopener">[ GO_TO_TARGET ]</a>
        <?php else: ?>
          <button class="command-button command-button-outline" type="button" disabled title="No VoltGear challenge is configured">[ GO_TO_TARGET ]</button>
        <?php endif; ?>
      </div>
      <p class="terminal-footer">STATUS: READY <span aria-hidden="true">|</span> SCOPE: VOLTGEAR_LAB_ONLY</p>
    </section>
  </main>
</body>
</html>
