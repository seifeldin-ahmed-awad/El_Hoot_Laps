<section class="listing-page order-page">
  <?php if ($order === null): ?>
    <div class="empty-state"><p class="eyebrow">ORDER NOT AVAILABLE</p><h1>This order is not in this session.</h1><a class="shop-button" href="<?= shop_e(shop_url('products')) ?>">Return to the shop</a></div>
  <?php else: ?>
    <p class="eyebrow">THANK YOU FOR SHOPPING WITH US</p>
    <div class="order-confirmation">
      <h1>Your order is placed.</h1>
      <p>Order BL-<?= shop_e(str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT)) ?> <span aria-hidden="true">·</span> <?= shop_e(date('F j, Y', strtotime($order['created_at']))) ?></p>
      <p>This is a local demonstration order. No payment or shipping details were collected.</p>
      <div class="order-summary">
        <?php foreach ($orderItems as $item): ?>
          <div class="summary-item"><span><?= shop_e($item['product_name']) ?> <small>× <?= (int) $item['quantity'] ?></small></span><strong>$<?= shop_e(number_format((float) $item['unit_price'] * (int) $item['quantity'], 2)) ?></strong></div>
        <?php endforeach; ?>
        <div class="summary-total"><span>Subtotal</span><strong>$<?= shop_e(number_format((float) $order['subtotal'], 2)) ?></strong></div>
      </div>
      <a class="text-link" href="<?= shop_e(shop_url('products')) ?>">Continue exploring <span aria-hidden="true">→</span></a>
    </div>
  <?php endif; ?>
</section>
