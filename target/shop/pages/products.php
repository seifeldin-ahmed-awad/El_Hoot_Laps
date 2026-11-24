<section class="listing-page">
  <p class="eyebrow">THE KANSO COLLECTION</p>
  <div class="section-heading listing-heading">
    <div><h1>Shop all</h1><?php if ($search !== ''): ?><p class="listing-context">Results for “<?= shop_e($search) ?>”</p><?php endif; ?></div>
    <form class="sort-form" method="get" action="index.php">
      <input type="hidden" name="page" value="<?= shop_e($page) ?>">
      <?php if ($categorySlug !== ''): ?><input type="hidden" name="category" value="<?= shop_e($categorySlug) ?>"><?php endif; ?>
      <?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= shop_e($search) ?>"><?php endif; ?>
      <label for="sort-products">Sort</label>
      <select id="sort-products" name="sort" data-auto-submit>
        <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
        <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Price: low to high</option>
        <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Price: high to low</option>
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
      </select>
    </form>
  </div>
  <nav class="filter-links listing-filters" aria-label="Filter products by category">
    <a class="<?= $categorySlug === '' ? 'is-selected' : '' ?>" href="<?= shop_e(shop_url('products', $search === '' ? [] : ['q' => $search])) ?>">All objects</a>
    <?php foreach ($categories as $category): ?>
      <a class="<?= $categorySlug === $category['slug'] ? 'is-selected' : '' ?>" href="<?= shop_e(shop_url('category', ['category' => $category['slug']])) ?>"><?= shop_e($category['name']) ?></a>
    <?php endforeach; ?>
  </nav>
  <?php if ($products === []): ?>
    <p class="empty-state">No objects matched that search. Try another name or category.</p>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
        <?php require __DIR__ . '/../includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
