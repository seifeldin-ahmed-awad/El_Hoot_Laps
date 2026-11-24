USE breachlab;

CREATE TABLE IF NOT EXISTS shop_categories (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(64) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_shop_categories_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shop_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id SMALLINT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    name VARCHAR(160) NOT NULL,
    product_code VARCHAR(32) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    image_class VARCHAR(32) NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_shop_products_slug (slug),
    UNIQUE KEY uq_shop_products_code (product_code),
    KEY idx_shop_products_category (category_id, is_featured),
    CONSTRAINT fk_shop_products_category
        FOREIGN KEY (category_id) REFERENCES shop_categories (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shop_orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_token CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    status ENUM('placed', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'placed',
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_shop_orders_token (order_token),
    KEY idx_shop_orders_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shop_order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(160) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_shop_order_items_order (order_id),
    CONSTRAINT fk_shop_order_items_order
        FOREIGN KEY (order_id) REFERENCES shop_orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_shop_order_items_product
        FOREIGN KEY (product_id) REFERENCES shop_products (id)
) ENGINE=InnoDB;

INSERT INTO shop_categories (slug, name, description) VALUES
    ('ceramics', 'Tableware & Ceramics', 'Quiet forms for daily rituals.'),
    ('lighting', 'Warm Lighting', 'Soft light, thoughtfully placed.'),
    ('textiles', 'Linen & Textiles', 'Natural textures for slower spaces.'),
    ('desk', 'Desk & Timber', 'Useful objects shaped by honest materials.')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'kyoto-stoneware-pitcher', 'Kyoto Stoneware Pitcher', 'KG-CER-014',
       'Hand-finished stoneware with a softly uneven glaze, made for water at the table and branches on the sill.',
       68.00, 12, 'pitcher', 1
FROM shop_categories WHERE slug = 'ceramics'
ON DUPLICATE KEY UPDATE
    slug = VALUES(slug);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'kobe-brass-dome-lamp', 'Kobe Brass Dome Lamp', 'KG-LGT-022',
       'A spun-brass shade and weighted base bring a warm, focused pool of light to a bedside or reading chair.',
       248.00, 6, 'lamp', 1
FROM shop_categories WHERE slug = 'lighting'
ON DUPLICATE KEY UPDATE
    slug = VALUES(slug);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'ghent-linen-throw', 'Ghent Linen Throw', 'KG-TEX-008',
       'Washed European linen with a relaxed drape and a subtle woven edge; softens with every season.',
       138.00, 18, 'throw', 1
FROM shop_categories WHERE slug = 'textiles'
ON DUPLICATE KEY UPDATE
    slug = VALUES(slug);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'nara-white-oak-valet-tray', 'Nara White Oak Valet Tray', 'KG-DSK-031',
       'Solid white oak, shaped with a shallow rim to keep the everyday essentials close at hand.',
       54.00, 10, 'tray', 1
FROM shop_categories WHERE slug = 'desk'
ON DUPLICATE KEY UPDATE
    slug = VALUES(slug);
