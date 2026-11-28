-- Lab 07: stored XSS in the product-review website field.
USE breachlab;

CREATE TABLE IF NOT EXISTS shop_product_reviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    review_body VARCHAR(2000) NOT NULL,
    website VARCHAR(500) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_shop_reviews_product (product_id, id),
    CONSTRAINT fk_shop_reviews_product
        FOREIGN KEY (product_id) REFERENCES shop_products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE labs
SET category = 'Cross-Site Scripting',
    title = 'Stored XSS into onclick Event with HTML-Encoded Angle Brackets and Double Quotes',
    description = 'Review stored website values rendered in an onclick event-handler attribute.',
    difficulty = 'Hard',
    points = 200,
    is_enabled = 1,
    expected_flag_hash = 'fd7b26b5a6216d453159265a2cd57ea2d1a38a98a7c9e695fff4a80c384cb4c6',
    expected_flag_hash_algorithm = 'sha256'
WHERE id = 7;
