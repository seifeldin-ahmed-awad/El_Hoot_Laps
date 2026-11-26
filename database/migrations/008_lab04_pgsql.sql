-- Lab 04 isolated PostgreSQL database: lab04_sqli
-- Run as a PostgreSQL administrator in database lab04_sqli.
CREATE TABLE IF NOT EXISTS tracking_sessions (id SERIAL PRIMARY KEY, tracking_id VARCHAR(128) NOT NULL);
INSERT INTO tracking_sessions(tracking_id) SELECT 'x' WHERE NOT EXISTS (SELECT 1 FROM tracking_sessions WHERE tracking_id='x');
GRANT USAGE ON SCHEMA public TO lab04_user;
GRANT SELECT ON tracking_sessions TO lab04_user;
