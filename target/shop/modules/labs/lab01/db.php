<?php
declare(strict_types=1);

/**
 * Lab 01 – Visible Error-Based SQL Injection
 * Isolated PostgreSQL connection for the lab01 challenge database.
 *
 * This connection is entirely separate from the main BreachLab MySQL database.
 * It uses a dedicated, least-privilege PostgreSQL role (lab01_user) rather
 * than the superuser account.
 *
 * ==========================================================================
 * REQUIRED SETUP (one-time, run as postgres superuser)
 * ==========================================================================
 *
 * 1. Create the application database:
 *      createdb -U postgres -h 127.0.0.1 lab01_sqli
 *
 * 2. Create the restricted application role:
 *      psql -U postgres -h 127.0.0.1 -d postgres -c "
 *        DO \$\$ BEGIN
 *          IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname='lab01_user') THEN
 *            CREATE ROLE lab01_user LOGIN PASSWORD 'lab01localdev';
 *          END IF;
 *        END \$\$;
 *        GRANT CONNECT ON DATABASE lab01_sqli TO lab01_user;"
 *
 * 3. Apply the migration (creates tables and grants table-level SELECT):
 *      psql -U postgres -h 127.0.0.1 -d lab01_sqli \
 *           -f database/migrations/005_lab01_pgsql.sql
 *
 * ==========================================================================
 * ENVIRONMENT VARIABLES
 * ==========================================================================
 *
 * Variable            Default          Notes
 * ------------------  ---------------  ------------------------------------
 * LAB01_PG_HOST       127.0.0.1        PostgreSQL host
 * LAB01_PG_PORT       5432             PostgreSQL port
 * LAB01_PG_DATABASE   lab01_sqli       Challenge database name
 * LAB01_PG_USER       lab01_user       Restricted application role
 * LAB01_PG_PASSWORD   lab01localdev    Change on any shared/network server
 *
 * Set these in Apache's httpd.conf or a .conf fragment, e.g.:
 *   SetEnv LAB01_PG_HOST      127.0.0.1
 *   SetEnv LAB01_PG_PORT      5432
 *   SetEnv LAB01_PG_DATABASE  lab01_sqli
 *   SetEnv LAB01_PG_USER      lab01_user
 *   SetEnv LAB01_PG_PASSWORD  lab01localdev
 *
 * The defaults are intentionally permissive for local development only.
 * Never expose the default password on a network-accessible server.
 */
function lab01_pdo(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $settings = config_lab_postgres(1);
    $host     = $settings['host'];
    $port     = $settings['port'];
    $database = $settings['database'];
    $user     = $settings['username'];
    $password = getenv('LAB01_PG_PASSWORD') !== false
        ? (string) getenv('LAB01_PG_PASSWORD')
        : 'lab01localdev';

    // Validate that port is numeric to prevent DSN injection.
    if (!ctype_digit($port)) {
        $port = '5432';
    }

    try {
        $connection = new PDO(
            "pgsql:host={$host};port={$port};dbname={$database}",
            $user,
            $password,
            [
                // ERRMODE_SILENT: query failures return false instead of
                // throwing.  lab01_process_tracking_cookie() calls errorInfo()
                // to surface the raw PostgreSQL message — that is intentional.
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        // Log the failure without exposing credentials in the error message.
        error_log(
            'Lab01: PostgreSQL connection failed. '
            . 'Check LAB01_PG_* environment variables and that the '
            . 'lab01_sqli database is running. PDO error code: '
            . $e->getCode()
        );
        // Re-throw a sanitised exception so callers can handle it cleanly.
        throw new RuntimeException(
            'Lab 01 database unavailable. Contact your platform administrator.',
            0,
            $e
        );
    }

    return $connection;
}
