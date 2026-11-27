<section class="listing-page account-page">
  <p class="eyebrow">YOUR KANSO SESSION</p>
  <div class="section-heading listing-heading"><h1>Your account</h1></div>
  <p class="account-note">Signed in as <strong><?= shop_e((string) ($_SESSION['shop_customer_email'] ?? '')) ?></strong>. Review your recent orders and account details.</p>
  <form method="post" action="index.php?page=account" class="account-logout-form"><input type="hidden" name="action" value="customer-logout"><?= csrf_field() ?><button type="submit">Sign out</button></form>
  <section class="account-email-panel" aria-labelledby="account-email-heading">
    <h2 class="subheading" id="account-email-heading">Email address</h2>
    <p>Current email: <strong><?= shop_e((string) ($_SESSION['csrf_lab08_email'] ?? ($_SESSION['shop_customer_email'] ?? ''))) ?></strong></p>
    <form method="post" action="index.php?page=account" class="account-email-form"><input type="hidden" name="action" value="change-email"><?= csrf_field() ?>
      <label for="account-email">New email address</label><input id="account-email" name="email" type="email" maxlength="190" required value="<?= shop_e((string) ($_SESSION['csrf_lab08_email'] ?? ($_SESSION['shop_customer_email'] ?? ''))) ?>"><button type="submit">Save email</button>
    </form>
  </section>
  <?php if (isset($_SESSION['csrf_lab08_solved_at'])): ?><section class="shop-flash is-success" id="csrf-lab08-flag" role="status" aria-live="polite"><strong>CSRF Lab 08 solved!</strong><p>Flag: <code>Flag{gLLzaG6xrUP3}</code></p><p>This success message will reset in 30 seconds.</p></section><script>window.setTimeout(() => window.location.reload(), 30000);</script><?php endif; ?>
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
