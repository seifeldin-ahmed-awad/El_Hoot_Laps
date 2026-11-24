<?php if ($product === null): http_response_code(404); ?>
  <section class="empty-state"><p class="eyebrow">OBJECT NOT FOUND</p><h1>This piece has moved on.</h1><a class="shop-button" href="<?= shop_e(shop_url('products')) ?>">Return to the collection</a></section>
<?php else: ?>
  <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= shop_e(shop_url()) ?>">Home</a><span>/</span><a href="<?= shop_e(shop_url('category', ['category' => $product['category_slug']])) ?>"><?= shop_e($product['category_name']) ?></a><span>/</span><span><?= shop_e($product['name']) ?></span></nav>
  <section class="product-detail">
    <div class="detail-art product-art art-<?= shop_e($product['image_class']) ?>" role="img" aria-label="<?= shop_e($product['name']) ?> in a warm studio still life">
      <span class="art-label"><?= shop_e($product['product_code']) ?></span><span class="art-object" aria-hidden="true"></span>
    </div>
    <div class="detail-copy">
      <p class="eyebrow"><?= shop_e($product['category_name']) ?> <span class="eyebrow-dot">·</span> <?= shop_e($product['product_code']) ?></p>
      <h1><?= shop_e($product['name']) ?></h1>
      <p class="detail-price">$<?= shop_e(number_format((float) $product['price'], 2)) ?></p>
      <p class="detail-description"><?= shop_e($product['description']) ?></p>
      <p class="stock-note"><?= (int) $product['stock'] > 0 ? 'In stock — ships in 2–3 studio days.' : 'Currently out of stock.' ?></p>
      <form class="add-form" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <label for="item-quantity">Quantity</label>
        <input id="item-quantity" name="quantity" type="number" value="1" min="1" max="<?= min(99, (int) $product['stock']) ?>" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
        <button class="shop-button" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>Add to bag</button>
      </form>
      <div class="detail-note"><span>01</span><p>Designed for everyday use. Small variations in material and finish are part of each piece.</p></div>
    </div>
  </section>
<?php endif; ?>
