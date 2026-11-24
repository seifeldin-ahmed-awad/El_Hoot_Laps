<section class="listing-page">
  <p class="eyebrow">SHOP BY MATERIAL</p>
  <div class="section-heading listing-heading">
    <div><h1><?= shop_e($selectedCategory['name'] ?? 'Collection') ?></h1><p class="listing-context"><?= shop_e($selectedCategory['description'] ?? 'Objects for everyday rituals.') ?></p></div>
    <a class="text-link" href="<?= shop_e(shop_url('products')) ?>">All objects <span aria-hidden="true">↗</span></a>
  </div>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php require __DIR__ . '/../includes/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>
