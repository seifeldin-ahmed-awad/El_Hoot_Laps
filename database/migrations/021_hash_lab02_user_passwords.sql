-- Hash the Lab 02 password values in an already-initialized lab02_sqli database.
-- Run against PostgreSQL database lab02_sqli as a database administrator.
UPDATE users
SET password = CASE username
    WHEN 'administrator' THEN md5('adminpass12345678901')
    WHEN 'production_manager' THEN md5('flag{abdelwahab}')
END
WHERE username IN ('administrator', 'production_manager');
