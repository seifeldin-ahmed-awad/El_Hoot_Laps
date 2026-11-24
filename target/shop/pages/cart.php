<section class="listing-page bag-page">
  <p class="eyebrow">A FEW THINGS, WELL CHOSEN</p>
  <div class="section-heading listing-heading"><h1>Your bag <span class="bag-count">(<?= shop_e(shop_cart_count()) ?>)</span></h1><a class="text-link" href="<?= shop_e(shop_url('products')) ?>">Continue browsing <span aria-hidden="true">→</span></a></div>
  <?php if ($cartItems === []): ?>
    <div class="empty-state"><p>Your bag is taking a quiet moment.</p><a class="shop-button" href="<?= shop_e(shop_url('products')) ?>">Explore the collection</a></div>
  <?php else: ?>
    <form method="post" id="update-cart">
      <?= csrf_field() ?><input type="hidden" name="action" value="update">
    </form>
    <div class="bag-layout">
      <div class="bag-lines">
        <?php foreach ($cartItems as $item): ?>
          <article class="bag-line">
            <a class="bag-art product-art art-<?= shop_e($item['image_class']) ?>" href="<?= shop_e(shop_url('product', ['slug' => $item['slug']])) ?>" aria-label="<?= shop_e($item['name']) ?>"><span class="art-object" aria-hidden="true"></span></a>
            <div class="bag-line-copy"><a class="product-name" href="<?= shop_e(shop_url('product', ['slug' => $item['slug']])) ?>"><?= shop_e($item['name']) ?></a><span class="product-category"><?= shop_e($item['product_code']) ?></span><span class="product-price">$<?= shop_e(number_format((float) $item['price'], 2)) ?></span></div>
            <label class="quantity-label" for="quantity-<?= (int) $item['id'] ?>">Qty</label>
            <input class="quantity-input" form="update-cart" id="quantity-<?= (int) $item['id'] ?>" name="quantities[<?= (int) $item['id'] ?>]" type="number" min="0" max="<?= min(99, (int) $item['stock']) ?>" value="<?= (int) $item['quantity'] ?>">
            <span class="line-total">$<?= shop_e(number_format((float) $item['line_total'], 2)) ?></span>
            <form method="post" class="remove-form">
              <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
              <button class="remove-button" type="submit">Remove</button>
            </form>
          </article>
        <?php endforeach; ?>
        <button class="text-link update-button" form="update-cart" type="submit">Update bag</button>
      </div>
      <aside class="order-summary">
        <p class="eyebrow">ORDER SUMMARY</p>
        <div><span>Subtotal</span><strong>$<?= shop_e(number_format($cartSubtotal, 2)) ?></strong></div>
        <p class="summary-note"><?= $cartSubtotal >= 150 ? 'Complimentary studio shipping.' : 'Shipping details are not collected in this local checkout.' ?></p>
        <a class="shop-button button-full" href="<?= shop_e(shop_url('checkout')) ?>">Continue to checkout</a>
        <p class="no-payment-note">No payment is collected. This is a local training storefront.</p>
      </aside>
    </div>
  <?php endif; ?>
</section>
