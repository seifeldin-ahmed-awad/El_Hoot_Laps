<?php
declare(strict_types=1);

/**
 * Lab 01 – Visible Error-Based SQL Injection
 *
 * TrackingId cookie handler.  The cookie value is inserted directly
 * into the SQL query without sanitisation, reproducing the
 * PortSwigger "Visible error-based SQL injection" lab behaviour.
 *
 * The PDO connection is set to ERRMODE_SILENT so that PHP does not
 * throw an exception.  Instead we call errorInfo() after each query
 * and re-surface the raw PostgreSQL error message – exactly as the
 * original lab does.
 *
 * Returns:  string|null  – the PostgreSQL error message, or null when
 *                          the query executes without error.
 */
function lab01_process_tracking_cookie(): ?string
{
    $trackingId = $_COOKIE['TrackingId'] ?? null;

    // No cookie present – nothing to do, no error.
    if ($trackingId === null) {
        return null;
    }

    require_once __DIR__ . '/db.php';
    $pdo = lab01_pdo();

    /*
     * Intentionally vulnerable query.
     * The TrackingId value is concatenated directly into the SQL
     * string.  This is the injection point.
     *
     * Equivalent to the original PortSwigger lab query:
     *   SELECT tracking_id FROM tracking_sessions
     *   WHERE tracking_id = '<user-supplied value>'
     */
    $sql = "SELECT tracking_id FROM tracking_sessions WHERE tracking_id = '" . $trackingId . "'";

    $result = $pdo->query($sql);

    if ($result === false) {
        // Surface the PostgreSQL error verbatim (reproduces lab behaviour).
        $errorInfo = $pdo->errorInfo();
        // errorInfo()[2] is the driver-specific error message.
        $driverMessage = $errorInfo[2] ?? 'Unknown database error.';
        return $driverMessage;
    }

    return null;
}
