<section class="shop-hero">
  <div class="hero-copy">
    <p class="eyebrow">OBJECTS FOR A CONSIDERED HOME</p>
    <h1>Quiet objects for<br>unhurried living.</h1>
    <p>Thoughtful forms, honest materials, and small rituals made a little more beautiful.</p>
    <a class="shop-button" href="<?= shop_e(shop_url('products')) ?>">Explore the collection <span aria-hidden="true">→</span></a>
  </div>
  <div class="hero-art" role="img" aria-label="Warm-toned studio still life of ceramics and natural materials">
    <span class="hero-sun"></span><span class="hero-vase"></span><span class="hero-book"></span>
    <span class="hero-caption">STUDY 04 — FORM & STILLNESS</span>
  </div>
</section>

<section class="shop-section category-section" aria-labelledby="category-title">
  <div class="section-heading">
    <div><p class="eyebrow">FIND YOUR MATERIAL</p><h2 id="category-title">Shop by category</h2></div>
    <a class="text-link" href="<?= shop_e(shop_url('products')) ?>">View everything <span aria-hidden="true">↗</span></a>
  </div>
  <div class="category-grid">
    <?php foreach ($categories as $index => $category): ?>
      <a class="category-card category-art-<?= (int) $index ?>" href="<?= shop_e(shop_url('category', ['category' => $category['slug']])) ?>">
        <span class="category-number">0<?= (int) ($index + 1) ?></span>
        <span class="category-shape" aria-hidden="true"></span>
        <span class="category-title"><?= shop_e($category['name']) ?></span>
        <span class="category-arrow" aria-hidden="true">↗</span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="shop-section collection-section" aria-labelledby="featured-title">
  <div class="section-heading">
    <div><p class="eyebrow">THE OBJECT EDIT</p><h2 id="featured-title">Considered essentials</h2></div>
    <div class="filter-links" aria-label="Filter featured collection">
      <a class="is-selected" href="<?= shop_e(shop_url('products')) ?>">All objects</a>
      <?php foreach ($categories as $category): ?>
        <a href="<?= shop_e(shop_url('category', ['category' => $category['slug']])) ?>"><?= shop_e(str_replace('Tableware & ', '', $category['name'])) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php require __DIR__ . '/../includes/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>

<section class="craft-section">
  <div class="craft-art" aria-hidden="true"><span class="craft-vessel"></span><span class="craft-caption">MADE TO LIVE WITH</span></div>
  <div class="craft-copy">
    <p class="eyebrow">MATERIAL, MADE MEANINGFUL</p>
    <h2>Built to gather<br>patina, not dust.</h2>
    <p>We look for pieces that feel right in the hand, age with grace, and earn their place in the everyday. Made in small runs with makers who care about what lasts.</p>
    <a class="text-link" href="<?= shop_e(shop_url('labs')) ?>">A note from the studio <span aria-hidden="true">→</span></a>
  </div>
</section>
