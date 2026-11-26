<?php
declare(strict_types=1);

/** Execute a TrackingId-based challenge through the Kanso Goods storefront request. */
function lab_shop_process(int $labId): array
{
    $suffix = str_pad((string) $labId, 2, '0', STR_PAD_LEFT);
    $host = getenv("LAB{$suffix}_PG_HOST") ?: '127.0.0.1';
    $port = getenv("LAB{$suffix}_PG_PORT") ?: '5432';
    $database = getenv("LAB{$suffix}_PG_DATABASE") ?: "lab{$suffix}_sqli";
    $user = getenv("LAB{$suffix}_PG_USER") ?: "lab{$suffix}_user";
    $password = getenv("LAB{$suffix}_PG_PASSWORD") ?: "lab{$suffix}_localdev";
    $result = ['lab_id' => $labId, 'message' => '', 'error' => '', 'duration' => 0.0, 'rows' => []];

    // Labs 02 and 04 use TrackingId. Lab 03 is handled by the main MySQL search query.
    $seed = $labId === 4 ? 'x' : 'xyz';
    $trackingId = (string) ($_COOKIE['TrackingId'] ?? $seed);
    if (!isset($_COOKIE['TrackingId'])) {
        setcookie('TrackingId', $seed, ['expires' => 0, 'path' => '/', 'httponly' => false, 'samesite' => 'Lax']);
        $_COOKIE['TrackingId'] = $seed;
    }

    try {
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // Lab 02 accepts changing normal TrackingId values. Seed the unmodified
        // prefix for this request with a parameterized statement, then retain the
        // intentionally vulnerable SELECT as the actual injection point.
        if ($labId === 2) {
            $baseTrackingId = explode("'", $trackingId, 2)[0];
            if ($baseTrackingId !== '' && strlen($baseTrackingId) <= 128) {
                // Use positional placeholders: PDO_PGSQL native prepares can
                // reject repeated named placeholders on some PHP builds.
                $seedStatement = $pdo->prepare(
                    'INSERT INTO tracking_sessions (tracking_id) '
                    . 'SELECT ? WHERE NOT EXISTS '
                    . '(SELECT 1 FROM tracking_sessions WHERE tracking_id = ?)'
                );
                $seedStatement->execute([$baseTrackingId, $baseTrackingId]);
            }
        }

        // Intentionally unsafe concatenation is the challenge's injection point.
        $sql = "SELECT tracking_id FROM tracking_sessions WHERE tracking_id = '" . $trackingId . "'\n";
        $started = microtime(true);
        $query = $pdo->query($sql);
        $result['duration'] = microtime(true) - $started;
        if ($query === false) {
            $result['error'] = (string) ($pdo->errorInfo()[2] ?? 'Database query failed.');
            return $result;
        }
        $result['rows'] = $query->fetchAll();
        if ($labId === 2 && count($result['rows']) > 0) {
            $result['message'] = 'Welcome back';
        } elseif ($labId === 4 && $result['duration'] >= 9.0) {
            $result['message'] = 'FLAG{youssef28}';
        }
    } catch (Throwable $e) {
        $result['error'] = 'Challenge database unavailable. Check the lab database setup.';
        error_log("Lab {$suffix} database error: " . $e->getMessage());
    }
    return $result;
}
