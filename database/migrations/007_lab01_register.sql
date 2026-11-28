-- =============================================================================
-- Migration 007: Register Lab 01 – Visible Error-Based SQL Injection.
-- =============================================================================
-- Apply to the MySQL breachlab database AFTER migration 006.
--
-- Changes from the previous 005_lab01_register.sql:
--   • expected_flag_hash  updated to the MD5 of f{EL-HOOT71026}
--   • expected_flag_hash_algorithm set to 'md5'
--   • is_enabled set to 1
--   • description updated to reflect the sales_manager account
--
-- The plaintext flag f{EL-HOOT71026} is stored only in the PostgreSQL
-- lab01_sqli.lab01_flag table and is never written to this MySQL database.
--
-- Flag  : f{EL-HOOT71026}
-- MD5   : 0de5ebb9999f9d50278265257898fb6b
-- =============================================================================

USE breachlab;

UPDATE labs
SET
    title                        = 'Visible Error-Based SQL Injection',
    category                     = 'SQL Injection',
    description                  = 'Exploit verbose database errors in a PostgreSQL-backed shop to extract the sales_manager credentials.',
    difficulty                   = 'Easy',
    points                       = 100,
    is_enabled                   = 1,
    expected_flag_hash           = '0de5ebb9999f9d50278265257898fb6b',
    expected_flag_hash_algorithm = 'md5'
WHERE id = 1;
