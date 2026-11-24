<article class="product-card">
  <a class="product-art art-<?= shop_e($product['image_class']) ?>" href="<?= shop_e(shop_url('product', ['slug' => $product['slug']])) ?>" aria-label="View <?= shop_e($product['name']) ?>">
    <span class="art-label"><?= shop_e($product['product_code']) ?></span>
    <span class="art-object" aria-hidden="true"></span>
  </a>
  <div class="product-card-copy">
    <div>
      <a class="product-name" href="<?= shop_e(shop_url('product', ['slug' => $product['slug']])) ?>"><?= shop_e($product['name']) ?></a>
      <p class="product-category"><?= shop_e($product['category_name']) ?></p>
    </div>
    <span class="product-price">$<?= shop_e(number_format((float) $product['price'], 2)) ?></span>
  </div>
</article>
