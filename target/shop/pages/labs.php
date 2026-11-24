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
  <h2>Training modules are not active in this shop.</h2>
  <p>These catalog entries are informational only. No intentionally vulnerable handlers or challenge flags are installed here. When an authorized, isolated lab implementation is ready, configure its route and server-side flag hash in the BreachLab platform; scoring remains on the existing Scoreboard.</p>
  <div class="lab-list">
    <?php foreach ($labs as $lab): ?>
      <article><span>LAB_<?= shop_e(str_pad((string) $lab['id'], 2, '0', STR_PAD_LEFT)) ?></span><h3><?= shop_e($lab['title']) ?></h3><p><?= shop_e($lab['description']) ?></p><small><?= shop_e($lab['difficulty']) ?> · <?= shop_e($lab['points']) ?> PTS · NOT CONFIGURED</small></article>
    <?php endforeach; ?>
  </div>
</section>
