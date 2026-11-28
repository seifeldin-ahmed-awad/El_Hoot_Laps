-- Correct the Lab 02 target in an already-initialized lab02_sqli database.
-- Run this against PostgreSQL database lab02_sqli as a database administrator.
UPDATE users
SET password = md5('flag{abdelwahab}'), role = 'production_manager'
WHERE username = 'production_manager';

-- Ensure the target exists if an older setup omitted it.
INSERT INTO users (username, password, role)
SELECT 'production_manager', md5('flag{abdelwahab}'), 'production_manager'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'production_manager'
);

GRANT SELECT ON users TO lab02_user;
