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
      <div class="stock-checker">
        <form method="get" action="index.php" id="check-stock-form">
          <input type="hidden" name="page" value="product">
          <input type="hidden" name="slug" value="<?= shop_e($product['slug']) ?>">
          <label for="stock-store">Choose a country/store</label>
          <select id="stock-store" name="storeId">
            <option value="UK"<?= ($_GET['storeId'] ?? 'UK') === 'UK' ? ' selected' : '' ?>>United Kingdom</option>
            <option value="US"<?= ($_GET['storeId'] ?? 'UK') === 'US' ? ' selected' : '' ?>>United States</option>
            <option value="France"<?= ($_GET['storeId'] ?? 'UK') === 'France' ? ' selected' : '' ?>>France</option>
          </select>
          <button class="shop-button" type="submit" name="checkStock" value="1" id="check-stock-button">Check stock</button>
          <input type="hidden" name="stockApi" value="<?= shop_e(config_lab09_stock_api()) ?>">
          <script>
            document.getElementById("check-stock-form").addEventListener("submit", async function (event) {
              event.preventDefault();
              const form = event.currentTarget;
              const params = new URLSearchParams();
              for (const [key, value] of new FormData(form).entries()) {
                if (key !== "stockApi") params.append(key, value);
              }
              params.set("checkStock", "1");
              window.history.pushState({}, "", "index.php?" + params.toString());

              const body = new URLSearchParams();
              body.append("csrf_token", <?= json_encode(csrf_token()) ?>);
              body.append("stockApi", form.elements.stockApi.value);
              const response = await fetch("index.php?" + params.toString(), {
                method: "POST",
                body
              });
              if (!response.ok) {
                throw new Error(await response.text());
              }
              document.open();
              document.write(await response.text());
              document.close();
            });
          </script>
        </form>
        <?php if (isset($_GET['checkStock']) && $ssrfResult !== null): ?>
          <p class="shop-flash <?= str_contains($ssrfResult, 'rejected') ? 'is-error' : 'is-success' ?>" id="ssrf-stock-result" role="status"><?= shop_e($ssrfResult) ?></p>
        <?php endif; ?>
        <?php if (($_GET['checkStock'] ?? '') === '1'): ?>
          <div id="stock-check-result" aria-live="polite">
            <script>
              // Intentionally vulnerable Lab 05 sink: URL search -> document.write.
              const storeId = new URLSearchParams(window.location.search).get('storeId') || 'UK';
              document.write('<select aria-label="Stock location"><option value="' + storeId + '">' + storeId + '</option></select>');
            </script>
          </div>
        <?php endif; ?>
      </div>
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


<?php if ($product !== null): ?>
  <section class="reviews-section" data-lab07>
    <p class="eyebrow">CUSTOMER NOTES</p>
    <h2>Reviews</h2>
    <?php if ($productReviews === []): ?>
      <p class="empty-state">No reviews yet. Be the first to share your experience.</p>
    <?php else: ?>
      <div class="review-list">
        <?php foreach ($productReviews as $review): ?>
          <article class="review-card">
            <h3>
              <?php
              // Lab 07 intentionally encodes angle brackets and double quotes for HTML,
              // while escaping single quotes and backslashes for the JavaScript string.
              // The &apos; entity remains decodable by the HTML parser.
              $lab07Website = str_replace(
                  ['<', '>', '"'],
                  ['&lt;', '&gt;', '&quot;'],
                  addslashes((string) $review['website'])
              );
              ?>
              <a href="#" onclick="window.location.href='<?= $lab07Website ?>'; return false;"><?= shop_e($review['reviewer_name']) ?></a>
            </h3>
            <p><?= shop_e($review['review_body']) ?></p>
            <small><?= shop_e($review['created_at']) ?></small>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form class="review-form" method="post" action="index.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="submit-review">
      <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
      <input type="hidden" name="product_slug" value="<?= shop_e($product['slug']) ?>">
      <h3>Write a review</h3>
      <label for="review-name">Name</label>
      <input id="review-name" name="review_name" type="text" maxlength="100" required>
      <label for="review-website">Website</label>
      <input id="review-website" name="review_website" type="text" maxlength="500" placeholder="https://example.com">
      <label for="review-body">Review</label>
      <textarea id="review-body" name="review_body" maxlength="2000" rows="4" required></textarea>
      <button class="shop-button" type="submit">Post review</button>
    </form>
  </section>
<?php endif; ?>
