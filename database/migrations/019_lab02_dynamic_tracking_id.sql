-- Remove fixed Lab 02 TrackingId seeds from existing lab02_sqli databases.
-- Apply to PostgreSQL database lab02_sqli.
DELETE FROM tracking_sessions
WHERE tracking_id IN ('xyz', 'ogAZZfxtOKUELbuJ');

GRANT SELECT, INSERT ON TABLE tracking_sessions TO lab02_user;
GRANT USAGE, SELECT ON SEQUENCE tracking_sessions_id_seq TO lab02_user;
