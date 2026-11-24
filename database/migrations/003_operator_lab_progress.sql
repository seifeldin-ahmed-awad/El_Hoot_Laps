USE breachlab;

CREATE TABLE IF NOT EXISTS operator_lab_progress (
    operator_id BIGINT UNSIGNED NOT NULL,
    lab_id TINYINT UNSIGNED NOT NULL,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (operator_id, lab_id),
    CONSTRAINT fk_progress_operator
        FOREIGN KEY (operator_id) REFERENCES operators (id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_lab
        FOREIGN KEY (lab_id) REFERENCES labs (id) ON DELETE CASCADE
) ENGINE=InnoDB;
