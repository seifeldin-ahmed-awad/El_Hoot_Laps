-- Lab 02 isolated PostgreSQL database: lab02_sqli
-- Run as a PostgreSQL administrator in database lab02_sqli.
CREATE TABLE IF NOT EXISTS tracking_sessions (id SERIAL PRIMARY KEY, tracking_id VARCHAR(128) NOT NULL);
CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY, username VARCHAR(64) UNIQUE NOT NULL, password VARCHAR(128) NOT NULL, role VARCHAR(32) NOT NULL);
INSERT INTO users(username,password,role) VALUES
 ('administrator',md5('adminpass12345678901'),'administrator'),
 ('production_manager',md5('flag{abdelwahab}'),'production_manager')
ON CONFLICT(username) DO UPDATE SET password=EXCLUDED.password, role=EXCLUDED.role;
GRANT USAGE ON SCHEMA public TO lab02_user;
GRANT SELECT ON tracking_sessions, users TO lab02_user;
GRANT INSERT ON tracking_sessions TO lab02_user;
GRANT USAGE, SELECT ON SEQUENCE tracking_sessions_id_seq TO lab02_user;
