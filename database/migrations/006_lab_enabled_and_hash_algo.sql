-- =============================================================================
-- Migration 006: Add is_enabled and expected_flag_hash_algorithm to labs.
-- =============================================================================
-- Apply to the MySQL breachlab database.
--
-- is_enabled
--   Replaces the hardcoded PHP condition ($labId === 1).
--   Default 0 keeps all existing un-configured labs unavailable.
--
-- expected_flag_hash_algorithm
--   Allows each lab to store its flag hash in a different algorithm.
--   Supported values: 'sha256' (default, 64-char hex) | 'md5' (32-char hex).
--   Existing labs with a configured expected_flag_hash use SHA-256 by default.
--
-- This migration is safe to run on existing installations.
-- Running it twice is harmless (the IF NOT EXISTS guards prevent errors).
-- =============================================================================

USE breachlab;

-- Add is_enabled column if it does not already exist.
SET @col_enabled_exists = (
    SELECT COUNT(*)
    FROM   information_schema.COLUMNS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'labs'
      AND  COLUMN_NAME  = 'is_enabled'
);

SET @add_enabled = IF(
    @col_enabled_exists = 0,
    'ALTER TABLE labs ADD COLUMN is_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER points',
    'SELECT 1 -- is_enabled already exists'
);
PREPARE _stmt FROM @add_enabled;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- Add expected_flag_hash_algorithm column if it does not already exist.
SET @col_algo_exists = (
    SELECT COUNT(*)
    FROM   information_schema.COLUMNS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'labs'
      AND  COLUMN_NAME  = 'expected_flag_hash_algorithm'
);

SET @add_algo = IF(
    @col_algo_exists = 0,
    "ALTER TABLE labs ADD COLUMN expected_flag_hash_algorithm ENUM('sha256','md5') NOT NULL DEFAULT 'sha256' AFTER expected_flag_hash",
    'SELECT 1 -- expected_flag_hash_algorithm already exists'
);
PREPARE _stmt FROM @add_algo;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- Widen expected_flag_hash to CHAR(64) if it is still CHAR(32).
-- (MD5 digests are 32 chars; we keep CHAR(64) so both fit without schema change.)
-- The column was already CHAR(64) from the original schema, so this is a no-op
-- on fresh installs.  Included for safety on any installation that may have
-- been created with CHAR(32).
SET @hash_char_len = (
    SELECT CHARACTER_MAXIMUM_LENGTH
    FROM   information_schema.COLUMNS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'labs'
      AND  COLUMN_NAME  = 'expected_flag_hash'
);

SET @widen_hash = IF(
    @hash_char_len < 64,
    'ALTER TABLE labs MODIFY COLUMN expected_flag_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL',
    'SELECT 1 -- expected_flag_hash is already wide enough'
);
PREPARE _stmt FROM @widen_hash;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;
