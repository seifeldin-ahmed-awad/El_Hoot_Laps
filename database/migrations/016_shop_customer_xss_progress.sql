-- Persist one-time XSS lab completions for each independent shop customer.
USE breachlab;

CREATE TABLE IF NOT EXISTS shop_customer_lab_completions (
    customer_id BIGINT UNSIGNED NOT NULL,
    lab_id TINYINT UNSIGNED NOT NULL,
    points_awarded SMALLINT UNSIGNED NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id, lab_id),
    KEY idx_shop_customer_lab_completions_lab (lab_id),
    CONSTRAINT fk_shop_customer_lab_completions_customer
        FOREIGN KEY (customer_id) REFERENCES shop_customers (id) ON DELETE CASCADE,
    CONSTRAINT fk_shop_customer_lab_completions_lab
        FOREIGN KEY (lab_id) REFERENCES labs (id) ON DELETE CASCADE
) ENGINE=InnoDB;
