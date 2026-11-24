<section class="listing-page checkout-page">
  <p class="eyebrow">A GENTLE FINAL CHECK</p>
  <div class="section-heading listing-heading"><h1>Checkout</h1><a class="text-link" href="<?= shop_e(shop_url('cart')) ?>">Return to your bag</a></div>
  <?php if ($cartItems === []): ?>
    <p class="empty-state">Your bag is empty. <a href="<?= shop_e(shop_url('products')) ?>">Browse the collection.</a></p>
  <?php else: ?>
    <div class="checkout-layout">
      <div class="checkout-copy">
        <h2>Studio order</h2>
        <p>This local demonstration records an order and adjusts catalog stock. It does not collect personal details, arrange shipping, or process payment.</p>
        <p class="checkout-security">Your bag will be reserved only when you place the order.</p>
        <form method="post">
          <?= csrf_field() ?><input type="hidden" name="action" value="place-order">
          <button class="shop-button" type="submit">Place demo order</button>
        </form>
      </div>
      <aside class="order-summary">
        <p class="eyebrow">IN YOUR BAG</p>
        <?php foreach ($cartItems as $item): ?>
          <div class="summary-item"><span><?= shop_e($item['name']) ?> <small>× <?= (int) $item['quantity'] ?></small></span><strong>$<?= shop_e(number_format((float) $item['line_total'], 2)) ?></strong></div>
        <?php endforeach; ?>
        <div class="summary-total"><span>Subtotal</span><strong>$<?= shop_e(number_format($cartSubtotal, 2)) ?></strong></div>
        <p class="summary-note">No additional payment or shipping charges apply in this demo.</p>
      </aside>
    </div>
  <?php endif; ?>
</section>
