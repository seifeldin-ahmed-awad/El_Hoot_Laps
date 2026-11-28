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
  <p>These challenges are integrated into this same storefront. Open the relevant shop pages and use a proxy tool or browser DevTools to interact with the injection points.</p>
  <div class="lab-list">
    <?php foreach ($labs as $lab): ?>
      <?php
      $labId   = (int) $lab['id'];
      $padded  = str_pad((string) $labId, 2, '0', STR_PAD_LEFT);
      $enabled = $lab['enabled'];
      $description = $labId === 2
          ? 'Use the TrackingId cookie on the normal storefront. A true condition displays "Welcome back"; a false condition does not. Use that signal to infer the production_manager password, then submit it as the flag in the BreachLab scoreboard.'
          : (string) $lab['description'];
      $shopPage = match ($labId) {
          1 => 'lab01',
          8 => 'account',
          default => 'products',
      };
      ?>
      <article class="<?= $enabled ? 'lab-enabled' : '' ?>">
        <span>LAB_<?= shop_e($padded) ?></span>
        <h3><?= shop_e($lab['title']) ?></h3>
        <p><?= shop_e($description) ?></p>
        <small><?= shop_e($lab['difficulty']) ?> · <?= shop_e($lab['points']) ?> PTS<?= $enabled ? ' · <span class="lab-status-on">ACTIVE</span>' : ' · NOT CONFIGURED' ?></small>
        <?php if ($enabled): ?>
          <div class="lab-actions">
            <a class="text-link" href="<?= shop_e(shop_url($shopPage)) ?>">Open in shop <span aria-hidden="true">→</span></a>
          </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
