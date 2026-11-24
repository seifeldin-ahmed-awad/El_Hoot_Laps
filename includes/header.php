<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/target.php';
require_once __DIR__ . '/leaderboard.php';

$user = $user ?? current_operator();
$userStanding = $userStanding ?? find_standing(
    $user === null ? [] : load_leaderboard(database()),
    $user
);
$targetLabId = $targetLabId ?? configured_target_lab_id();
$activePage = $activePage ?? '';
?>
<header class="topbar">
  <a class="brand" href="index.php">[BREACHLAB://CTF_v2.4]</a>
  <nav class="navigation" aria-label="Main navigation">
      <a class="nav-link<?= $activePage === 'intro' ? ' is-current' : '' ?>" href="index.php"<?= $activePage === 'intro' ? ' aria-current="page"' : '' ?>>[01_INTRO]</a>
      <a class="nav-link<?= $activePage === 'scoreboard' ? ' is-current' : '' ?>" href="scoreboard.php"<?= $activePage === 'scoreboard' ? ' aria-current="page"' : '' ?>>[02_SCOREBOARD]</a>
      <?php if ($targetLabId !== null): ?>
          <a class="nav-link" href="open_challenge.php?lab_id=<?= (int) $targetLabId ?>" target="_blank" rel="noopener">[03_TARGET]</a>
      <?php else: ?>
          <span class="nav-link nav-link-unavailable" aria-disabled="true" title="No VoltGear challenge is configured">[03_TARGET]</span>
      <?php endif; ?>
  </nav>
  <?php if ($user !== null): ?>
      <div class="operator-summary" aria-label="Current operator stats">
          <span>USR: <?= e($user['username']) ?></span>
          <?php if ($userStanding !== null): ?>
              <span class="points-badge">PTS: <?= e(number_format($userStanding['score'])) ?> <span aria-hidden="true">|</span> RANK: #<?= e($userStanding['rank']) ?></span>
          <?php endif; ?>
          <form method="post" action="logout.php" class="logout-form">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <button class="command-button logout-button" type="submit">[LOGOUT]</button>
          </form>
      </div>
  <?php else: ?>
      <div class="operator-summary">
          <a class="nav-link" href="login.php">[LOGIN]</a>
          <a class="nav-link" href="register.php">[SIGN_UP]</a>
      </div>
  <?php endif; ?>
</header>
