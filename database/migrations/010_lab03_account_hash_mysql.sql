-- Ensure the Lab 03 target account exists in the MariaDB users table.
-- Existing tables are left untouched.
USE breachlab;

CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(80) NOT NULL,
    password CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (username)
) ENGINE=InnoDB;

INSERT INTO users (username, password)
VALUES ('mariam', '35539c649159dbdb1de98dd905088ae9')
ON DUPLICATE KEY UPDATE password = VALUES(password);
