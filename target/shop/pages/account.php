<section class="listing-page account-page">
  <p class="eyebrow">YOUR KANSO SESSION</p>
  <div class="section-heading listing-heading"><h1>Your account</h1></div>
  <p class="account-note">This local storefront uses a private browser session for your bag and recent demo orders. It does not create a separate customer profile.</p>
  <h2 class="subheading">Recent orders</h2>
  <?php if ($orderHistory === []): ?>
    <p class="empty-state">No orders in this session yet. <a href="<?= shop_e(shop_url('products')) ?>">Explore the collection.</a></p>
  <?php else: ?>
    <div class="history-list">
      <?php foreach ($orderHistory as $historyOrder): ?>
        <a class="history-row" href="<?= shop_e(shop_url('order', ['id' => (int) $historyOrder['id']])) ?>">
          <span>BL-<?= shop_e(str_pad((string) $historyOrder['id'], 6, '0', STR_PAD_LEFT)) ?><small><?= shop_e(date('F j, Y', strtotime($historyOrder['created_at']))) ?></small></span>
          <span><?= shop_e(ucfirst($historyOrder['status'])) ?></span>
          <strong>$<?= shop_e(number_format((float) $historyOrder['subtotal'], 2)) ?></strong>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
