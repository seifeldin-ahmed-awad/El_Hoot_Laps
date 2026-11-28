-- Migration 005: Register Lab 01 in the BreachLab platform.
-- Stores the expected flag hash for Lab 01 – Visible Error-Based SQL Injection.
--
-- The expected_flag_hash is the SHA-256 of the plaintext flag stored in
-- the isolated PostgreSQL challenge database (lab01_sqli.lab01_flag).
--
-- Run once after executing database/schema.sql.

USE breachlab;

UPDATE labs
SET
    title               = 'Visible Error-Based SQL Injection',
    category            = 'SQL Injection',
    description         = 'Exploit verbose database errors to extract the sales_manager password from a PostgreSQL-backed shop.',
    difficulty          = 'Easy',
    points              = 100,
    expected_flag_hash  = '52ac4bdbbd2501de89617d1c4b7b3bdd62ce193c6badc76d2ab14f50c852d939'
WHERE id = 1;
