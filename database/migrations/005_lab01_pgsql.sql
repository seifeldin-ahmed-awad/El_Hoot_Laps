-- =============================================================================
-- BreachLab – Lab 01 PostgreSQL migration
-- Database : lab01_sqli  (PostgreSQL, NOT the MySQL breachlab database)
-- =============================================================================
--
-- USAGE
-- -----
-- 1. Ensure PostgreSQL is running and the lab01_sqli database exists:
--
--      createdb -U postgres -h 127.0.0.1 lab01_sqli
--
-- 2. Create the restricted application user (run once):
--
--      psql -U postgres -h 127.0.0.1 -d postgres -c "
--        DO \$\$ BEGIN
--          IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname='lab01_user') THEN
--            CREATE ROLE lab01_user LOGIN PASSWORD 'lab01localdev';
--          END IF;
--        END \$\$;
--        GRANT CONNECT ON DATABASE lab01_sqli TO lab01_user;"
--
-- 3. Apply this migration:
--
--      psql -U postgres -h 127.0.0.1 -d lab01_sqli \
--           -f database/migrations/005_lab01_pgsql.sql
--
-- 4. Grant table-level permissions to the application user:
--
--      psql -U postgres -h 127.0.0.1 -d lab01_sqli -c "
--        GRANT SELECT ON tracking_sessions TO lab01_user;
--        GRANT SELECT ON lab01_flag TO lab01_user;
--        GRANT SELECT ON users TO lab01_user;"
--
-- This migration is idempotent: re-running it will not create duplicate rows
-- or raise an error.
--
-- Environment variables expected by target/shop/modules/labs/lab01/db.php:
--   LAB01_PG_HOST      (default: 127.0.0.1)
--   LAB01_PG_PORT      (default: 5432)
--   LAB01_PG_DATABASE  (default: lab01_sqli)
--   LAB01_PG_USER      (default: lab01_user)
--   LAB01_PG_PASSWORD  (default: lab01localdev  — change on any shared server)
--
-- IMPORTANT: sales_manager is a dummy local training account only.
-- Do not reuse f{EL-HOOT71026} for real accounts.
-- =============================================================================


-- ---------------------------------------------------------------------------
-- Table: users
-- Stores the training accounts whose credentials are revealed through the
-- error-based injection technique.
--
-- Row order matters for the LIMIT 1 extraction payload:
--   ' AND 1=CAST((SELECT username FROM users LIMIT 1) AS int)--
-- The first row (lowest id) must be sales_manager so that payload reveals it.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id       SERIAL PRIMARY KEY,
    username VARCHAR(64)  NOT NULL,
    password VARCHAR(128) NOT NULL,
    role     VARCHAR(32)  NOT NULL DEFAULT 'user'
);

-- Add role column to existing installs that pre-date this migration.
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE  table_schema = 'public'
          AND  table_name   = 'users'
          AND  column_name  = 'role'
    ) THEN
        ALTER TABLE users ADD COLUMN role VARCHAR(32) NOT NULL DEFAULT 'user';
    END IF;
END $$;

-- Seed training accounts.
-- sales_manager MUST be the first row (lowest id) so that
--   ' AND 1=CAST((SELECT username FROM users LIMIT 1) AS int)--
-- reveals 'sales_manager' in the type-conversion error message.
--
-- Strategy: if the table is empty (fresh install), insert in the correct
-- order.  If rows already exist but sales_manager is not first, truncate
-- and re-seed — all rows are dummy training accounts.
DO $$
DECLARE
    v_first_username TEXT;
    v_count          INT;
BEGIN
    SELECT COUNT(*) INTO v_count FROM users;

    IF v_count = 0 THEN
        -- Fresh install: insert in the required order.
        INSERT INTO users (username, password, role) VALUES
            ('sales_manager', 'f{EL-HOOT71026}', 'sales_manager'),
            ('wiener',        'peter',            'user'),
            ('carlos',        'montoya',          'user');

    ELSE
        -- Existing install: check whether sales_manager is first.
        SELECT username INTO v_first_username
        FROM   users ORDER BY id LIMIT 1;

        IF v_first_username <> 'sales_manager' THEN
            -- Re-seed: truncate and reinsert in the correct order.
            -- These are all dummy local training accounts; no real data is lost.
            TRUNCATE TABLE users RESTART IDENTITY;
            INSERT INTO users (username, password, role) VALUES
                ('sales_manager', 'f{EL-HOOT71026}', 'sales_manager'),
                ('wiener',        'peter',            'user'),
                ('carlos',        'montoya',          'user');
        ELSE
            -- sales_manager is already first; ensure password and role are current.
            UPDATE users SET password = 'f{EL-HOOT71026}', role = 'sales_manager'
            WHERE  username = 'sales_manager';

            -- Add wiener / carlos if they are missing.
            INSERT INTO users (username, password, role)
            SELECT 'wiener', 'peter', 'user'
            WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'wiener');

            INSERT INTO users (username, password, role)
            SELECT 'carlos', 'montoya', 'user'
            WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'carlos');
        END IF;
    END IF;
END $$;

-- ---------------------------------------------------------------------------
-- Table: tracking_sessions
-- Each row is a known-good TrackingId value.  The vulnerable query looks up
-- the supplied cookie value in this table.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tracking_sessions (
    id          SERIAL PRIMARY KEY,
    tracking_id VARCHAR(128) NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);

INSERT INTO tracking_sessions (tracking_id)
SELECT 'ogAZZfxtOKUELbuJ'
WHERE NOT EXISTS (
    SELECT 1 FROM tracking_sessions WHERE tracking_id = 'ogAZZfxtOKUELbuJ'
);

-- ---------------------------------------------------------------------------
-- Table: lab01_flag
-- Stores the plaintext flag displayed to the learner after they successfully
-- authenticate as sales_manager.  The corresponding MD5 digest is stored in
-- the MySQL breachlab.labs table (expected_flag_hash).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lab01_flag (
    id         SERIAL PRIMARY KEY,
    flag_value VARCHAR(256) NOT NULL
);

-- Remove the previous flag value if it differs from the current one,
-- then insert the correct value if the table is empty.
DELETE FROM lab01_flag
WHERE flag_value <> 'f{EL-HOOT71026}';

INSERT INTO lab01_flag (flag_value)
SELECT 'f{EL-HOOT71026}'
WHERE NOT EXISTS (SELECT 1 FROM lab01_flag);

-- ---------------------------------------------------------------------------
-- Grant table permissions to the restricted application user.
-- These are idempotent GRANT statements; re-running them is harmless.
-- ---------------------------------------------------------------------------
GRANT USAGE  ON SCHEMA public              TO lab01_user;
GRANT SELECT ON TABLE tracking_sessions    TO lab01_user;
GRANT SELECT ON TABLE lab01_flag           TO lab01_user;
GRANT SELECT ON TABLE users                TO lab01_user;
