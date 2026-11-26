USE breachlab;

-- Repair migration for installations where the four shop categories exist
-- but the product seed rows from 004_shop_catalog.sql are missing.
-- Safe to run more than once; does not modify any SQLi lab tables or data.

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'kyoto-stoneware-pitcher', 'Kyoto Stoneware Pitcher', 'KG-CER-014',
       'Hand-finished stoneware with a softly uneven glaze, made for water at the table and branches on the sill.',
       68.00, 12, 'pitcher', 1
FROM shop_categories WHERE slug = 'ceramics'
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id), name = VALUES(name),
    product_code = VALUES(product_code), description = VALUES(description),
    price = VALUES(price), stock = VALUES(stock), image_class = VALUES(image_class),
    is_featured = VALUES(is_featured);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'kobe-brass-dome-lamp', 'Kobe Brass Dome Lamp', 'KG-LGT-022',
       'A spun-brass shade and weighted base bring a warm, focused pool of light to a bedside or reading chair.',
       248.00, 6, 'lamp', 1
FROM shop_categories WHERE slug = 'lighting'
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id), name = VALUES(name),
    product_code = VALUES(product_code), description = VALUES(description),
    price = VALUES(price), stock = VALUES(stock), image_class = VALUES(image_class),
    is_featured = VALUES(is_featured);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'ghent-linen-throw', 'Ghent Linen Throw', 'KG-TEX-008',
       'Washed European linen with a relaxed drape and a subtle woven edge; softens with every season.',
       138.00, 18, 'throw', 1
FROM shop_categories WHERE slug = 'textiles'
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id), name = VALUES(name),
    product_code = VALUES(product_code), description = VALUES(description),
    price = VALUES(price), stock = VALUES(stock), image_class = VALUES(image_class),
    is_featured = VALUES(is_featured);

INSERT INTO shop_products
    (category_id, slug, name, product_code, description, price, stock, image_class, is_featured)
SELECT id, 'nara-white-oak-valet-tray', 'Nara White Oak Valet Tray', 'KG-DSK-031',
       'Solid white oak, shaped with a shallow rim to keep the everyday essentials close at hand.',
       54.00, 10, 'tray', 1
FROM shop_categories WHERE slug = 'desk'
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id), name = VALUES(name),
    product_code = VALUES(product_code), description = VALUES(description),
    price = VALUES(price), stock = VALUES(stock), image_class = VALUES(image_class),
    is_featured = VALUES(is_featured);

-- Expected result: 4 rows in shop_products, one for each shop category.
