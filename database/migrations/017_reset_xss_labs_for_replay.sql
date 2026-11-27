-- One-time reset of XSS lab state so Labs 05-07 can be replayed.
-- This deletes stored review submissions and customer completion records.
-- It does not remove the Reviews feature or disable the vulnerable lab entry points.
USE breachlab;

DELETE FROM shop_product_reviews;
DELETE FROM shop_customer_lab_completions WHERE lab_id IN (5, 6, 7);
