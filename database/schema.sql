CREATE DATABASE IF NOT EXISTS breachlab
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE breachlab;

CREATE TABLE IF NOT EXISTS operators (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(32) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_operators_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS labs (
    id TINYINT UNSIGNED NOT NULL,
    category VARCHAR(64) NOT NULL,
    title VARCHAR(160) NOT NULL,
    description VARCHAR(255) NOT NULL,
    difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL,
    points SMALLINT UNSIGNED NOT NULL,
    expected_flag_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_labs_expected_flag_hash (expected_flag_hash),
    KEY idx_labs_category (category, id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS flag_submissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    operator_id BIGINT UNSIGNED NOT NULL,
    lab_id TINYINT UNSIGNED NULL DEFAULT NULL,
    submitted_flag_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    is_correct TINYINT(1) NOT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_submissions_operator_lab_date (operator_id, lab_id, submitted_at),
    CONSTRAINT fk_submissions_operator
        FOREIGN KEY (operator_id) REFERENCES operators (id) ON DELETE CASCADE,
    CONSTRAINT fk_submissions_lab
        FOREIGN KEY (lab_id) REFERENCES labs (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lab_completions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    operator_id BIGINT UNSIGNED NOT NULL,
    lab_id TINYINT UNSIGNED NOT NULL,
    points_awarded SMALLINT UNSIGNED NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_completion_operator_lab (operator_id, lab_id),
    KEY idx_completions_lab (lab_id),
    CONSTRAINT fk_completions_operator
        FOREIGN KEY (operator_id) REFERENCES operators (id) ON DELETE CASCADE,
    CONSTRAINT fk_completions_lab
        FOREIGN KEY (lab_id) REFERENCES labs (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW operator_scores AS
SELECT
    o.id AS operator_id,
    o.username,
    COALESCE(SUM(c.points_awarded), 0) AS total_points,
    COUNT(c.id) AS completed_labs
FROM operators AS o
LEFT JOIN lab_completions AS c ON c.operator_id = o.id
GROUP BY o.id, o.username;

INSERT INTO labs (id, category, title, description, difficulty, points) VALUES
    (1, 'SQL Injection', 'Error-based SQL Injection', 'Explore how database error handling can reveal details about a query.', 'Easy', 100),
    (2, 'SQL Injection', 'Blind Conditional SQL Injection', 'Study how changes in application responses can disclose a hidden query result.', 'Medium', 150),
    (3, 'SQL Injection', 'UNION-based SQL Injection', 'Examine how combining query results can expose data beyond the intended view.', 'Medium', 150),
    (4, 'SQL Injection', 'Time-based Blind SQL Injection', 'Investigate how response timing can reveal information about a database query.', 'Hard', 200),
    (5, 'Cross-Site Scripting', 'DOM XSS in document.write', 'Review client-side rendering that writes page content from browser-controlled data.', 'Medium', 150),
    (6, 'Cross-Site Scripting', 'Reflected XSS in JavaScript String', 'Learn why reflected input must be handled safely in JavaScript string contexts.', 'Medium', 150),
    (7, 'Cross-Site Scripting', 'Stored XSS in onclick Event', 'Examine the risks of rendering stored content in an event-handler context.', 'Hard', 200),
    (8, 'CSRF', 'CSRF Token Validation', 'Understand how anti-CSRF tokens protect state-changing requests.', 'Medium', 150),
    (9, 'SSRF', 'SSRF with Blacklist Filter', 'Study the limitations of URL deny-lists when restricting server-side requests.', 'Hard', 200),
    (10, 'Clickjacking', 'Basic Clickjacking', 'Learn how framing controls help protect pages from clickjacking.', 'Easy', 100)
ON DUPLICATE KEY UPDATE
    category = VALUES(category),
    title = VALUES(title),
    description = VALUES(description),
    difficulty = VALUES(difficulty),
    points = VALUES(points);


-- Lab 03 UNION SQL injection challenge data (same database as the storefront).
-- Only the target username and password hash are stored.
CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(80) NOT NULL,
    password CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (username)
) ENGINE=InnoDB;

INSERT INTO users (username, password)
VALUES ('mariam', '35539c649159dbdb1de98dd905088ae9')
ON DUPLICATE KEY UPDATE password = VALUES(password);
