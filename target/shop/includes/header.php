<div class="announcement">
  <span>Autumn Studio Release — Free shipping over $150</span>
  <a href="<?= shop_e(shop_url('products', ['sort' => 'featured'])) ?>">Explore new arrivals <span aria-hidden="true">→</span></a>
</div>
<header class="shop-header">
  <a class="shop-brand" href="<?= shop_e(shop_url()) ?>">Kanso Goods</a>
  <nav class="shop-navigation" aria-label="Shop navigation">
    <a href="<?= shop_e(shop_url('products')) ?>">Shop All</a>
    <?php foreach (array_slice($categories, 0, 3) as $category): ?>
      <a href="<?= shop_e(shop_url('category', ['category' => $category['slug']])) ?>"><?= shop_e($category['name'] === 'Tableware & Ceramics' ? 'Ceramics' : (str_contains($category['name'], 'Lighting') ? 'Lighting' : 'Textiles')) ?></a>
    <?php endforeach; ?>
    <a href="<?= shop_e(shop_url('labs')) ?>">Studio Journal</a>
  </nav>
  <div class="shop-utilities">
    <form class="search-form" method="get" action="index.php" role="search">
      <input type="hidden" name="page" value="products">
      <label class="sr-only" for="shop-search">Search products</label>
      <input id="shop-search" name="q" type="search" value="<?= shop_e($search) ?>" placeholder="Search">
      <button type="submit" aria-label="Submit search">⌕</button>
    </form>
    <a href="<?= shop_e(shop_url('account')) ?>">Account</a>
    <a class="bag-link" href="<?= shop_e(shop_url('cart')) ?>">Bag <span>(<?= shop_e(shop_cart_count()) ?>)</span></a>
  </div>
</header>
