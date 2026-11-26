-- Lab 02 isolated PostgreSQL database: lab02_sqli
-- Run as a PostgreSQL administrator in database lab02_sqli.
CREATE TABLE IF NOT EXISTS tracking_sessions (id SERIAL PRIMARY KEY, tracking_id VARCHAR(128) NOT NULL);
INSERT INTO tracking_sessions(tracking_id) SELECT 'xyz' WHERE NOT EXISTS (SELECT 1 FROM tracking_sessions WHERE tracking_id='xyz');
-- Also accept the storefront's original default TrackingId so learners can keep using their existing shop cookie.
INSERT INTO tracking_sessions(tracking_id) SELECT 'ogAZZfxtOKUELbuJ' WHERE NOT EXISTS (SELECT 1 FROM tracking_sessions WHERE tracking_id='ogAZZfxtOKUELbuJ');
CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY, username VARCHAR(64) UNIQUE NOT NULL, password VARCHAR(128) NOT NULL, role VARCHAR(32) NOT NULL);
INSERT INTO users(username,password,role) VALUES
 ('administrator','adminpass12345678901','administrator'),
 ('production_manager','flag{abdelwahab}','production_manager')
ON CONFLICT(username) DO UPDATE SET password=EXCLUDED.password, role=EXCLUDED.role;
GRANT USAGE ON SCHEMA public TO lab02_user;
GRANT SELECT ON tracking_sessions, users TO lab02_user;
GRANT INSERT ON tracking_sessions TO lab02_user;
GRANT USAGE, SELECT ON SEQUENCE tracking_sessions_id_seq TO lab02_user;
