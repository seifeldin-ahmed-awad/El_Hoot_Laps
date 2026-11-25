<section class="listing-page journal-page">
  <p class="eyebrow">FROM THE KANSO STUDIO</p>
  <h1>Notes on living with less, and living with it well.</h1>
  <div class="journal-feature">
    <div class="journal-art" aria-hidden="true"><span class="journal-object"></span></div>
    <div><p class="eyebrow">MATERIAL STUDY — 01</p><h2>Built to gather patina, not dust.</h2><p>We believe the most useful objects are the ones that stay close: touched, repaired, and made richer by the everyday.</p><a class="text-link" href="<?= shop_e(shop_url('products')) ?>">Explore the collection <span aria-hidden="true">→</span></a></div>
  </div>
</section>
<section class="lab-notice">
  <p class="eyebrow">BREACHLAB TRAINING REGISTRY</p>
  <h2>Active training modules.</h2>
  <p>These are intentionally vulnerable challenge modules running on this shop. Each challenge is isolated. Use a proxy tool or browser DevTools to interact with the injection points.</p>
  <div class="lab-list">
    <?php foreach ($labs as $lab): ?>
      <?php
      $labId   = (int) $lab['id'];
      $padded  = str_pad((string) $labId, 2, '0', STR_PAD_LEFT);
      $enabled = $lab['enabled'];
      ?>
      <article class="<?= $enabled ? 'lab-enabled' : '' ?>">
        <span>LAB_<?= shop_e($padded) ?></span>
        <h3><?= shop_e($lab['title']) ?></h3>
        <p><?= shop_e($lab['description']) ?></p>
        <small><?= shop_e($lab['difficulty']) ?> · <?= shop_e($lab['points']) ?> PTS<?= $enabled ? ' · <span class="lab-status-on">ACTIVE</span>' : ' · NOT CONFIGURED' ?></small>
        <?php if ($enabled): ?>
          <div class="lab-actions">
            <a class="text-link" href="<?= shop_e(shop_url('lab0' . $labId)) ?>">Challenge info <span aria-hidden="true">→</span></a>
            <a class="text-link" href="modules/labs/lab0<?= $labId ?>/login.php" target="_blank" rel="noopener">Open target <span aria-hidden="true">↗</span></a>
          </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<style>
.lab-list article.lab-enabled {
  border-color: #b8c8bc;
  background: #f5faf7;
}
.lab-status-on {
  color: var(--success);
  font-family: "Red Hat Mono", Consolas, monospace;
}
.lab-actions {
  display: flex;
  gap: 18px;
  margin-top: 10px;
}
</style>
