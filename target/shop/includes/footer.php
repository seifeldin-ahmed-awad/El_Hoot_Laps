<footer class="shop-footer">
  <a class="shop-brand footer-brand" href="<?= shop_e(shop_url()) ?>">Kanso Goods</a>
  <div class="footer-column">
    <h2>Shop</h2>
    <a href="<?= shop_e(shop_url('products')) ?>">Shop all</a>
    <a href="<?= shop_e(shop_url('category', ['category' => 'ceramics'])) ?>">Ceramics</a>
    <a href="<?= shop_e(shop_url('category', ['category' => 'lighting'])) ?>">Lighting</a>
    <a href="<?= shop_e(shop_url('category', ['category' => 'textiles'])) ?>">Textiles</a>
  </div>
  <div class="footer-column">
    <h2>Studio</h2>
    <a href="<?= shop_e(shop_url('labs')) ?>">Studio Journal</a>
    <a href="<?= shop_e(shop_url('account')) ?>">Your account</a>
  </div>
  <div class="footer-column">
    <h2>Support</h2>
    <a href="<?= shop_e(shop_url('info')) ?>#shipping">Shipping information</a>
    <a href="<?= shop_e(shop_url('info')) ?>#returns">Returns</a>
    <a href="<?= shop_e(shop_url('info')) ?>#contact">Contact</a>
  </div>
  <p class="footer-note">A local training storefront. No real payments or customer services.</p>
</footer>
