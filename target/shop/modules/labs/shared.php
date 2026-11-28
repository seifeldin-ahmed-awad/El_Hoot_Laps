<?php
declare(strict_types=1);

/** Execute a TrackingId-based challenge through the Kanso Goods storefront request. */
function lab_shop_process(int $labId): array
{
    $suffix = str_pad((string) $labId, 2, '0', STR_PAD_LEFT);
    $settings = config_lab_postgres($labId);
    $host = $settings['host'];
    $port = $settings['port'];
    $database = $settings['database'];
    $user = $settings['username'];
    $password = $settings['password'];
    $result = ['lab_id' => $labId, 'message' => '', 'error' => '', 'duration' => 0.0, 'rows' => []];

    // Labs 02 and 04 use TrackingId. Lab 03 is handled by the main MySQL search query.
    if ($labId === 2) {
        // Preserve the ordinary cookie value as the session's baseline.
        // If the cookie has already been modified for the exercise, retain
        // the part before the SQL quote; never substitute a hard-coded ID.
        $cookieValue = (string) ($_COOKIE['TrackingId'] ?? '');
        $baseFromCookie = explode("'", $cookieValue, 2)[0];
        $targetTrackingId = $_SESSION['lab02_tracking_id'] ?? null;
        if (!is_string($targetTrackingId) || preg_match('/\\A[A-Za-z0-9]{16,64}\\z/', $targetTrackingId) !== 1) {
            $targetTrackingId = preg_match('/\\A[A-Za-z0-9]{16,64}\\z/', $baseFromCookie) === 1
                ? $baseFromCookie
                : bin2hex(random_bytes(16));
            $_SESSION['lab02_tracking_id'] = $targetTrackingId;
        }
        $trackingId = $cookieValue !== '' ? $cookieValue : $targetTrackingId;
        if (!isset($_COOKIE['TrackingId'])) {
            setcookie('TrackingId', $targetTrackingId, ['expires' => 0, 'path' => '/', 'httponly' => false, 'samesite' => 'Lax']);
            $_COOKIE['TrackingId'] = $targetTrackingId;
        }
    } else {
        $seed = $labId === 4 ? 'x' : bin2hex(random_bytes(12));
        $trackingId = (string) ($_COOKIE['TrackingId'] ?? $seed);
        if (!isset($_COOKIE['TrackingId'])) {
            setcookie('TrackingId', $seed, ['expires' => 0, 'path' => '/', 'httponly' => false, 'samesite' => 'Lax']);
            $_COOKIE['TrackingId'] = $seed;
        }
    }

    try {
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if ($labId === 2) {
            $seedStatement = $pdo->prepare(
                'INSERT INTO tracking_sessions (tracking_id) '
                . 'SELECT ? WHERE NOT EXISTS '
                . '(SELECT 1 FROM tracking_sessions WHERE tracking_id = ?)'
            );
            if (!$seedStatement->execute([$targetTrackingId, $targetTrackingId])) {
                $result['error'] = (string) ($seedStatement->errorInfo()[2] ?? 'Lab 02 target initialization failed.');
                return $result;
            }
        }

        // The Lab 02 app role can read users, so blind predicates can target production_manager's password.
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
            $result['message'] = 'MD5: ' . md5('FLAG{youssef28}');
        }
    } catch (Throwable $e) {
        $result['error'] = 'Challenge database unavailable. Check the lab database setup.';
        error_log("Lab {$suffix} database error: " . $e->getMessage());
    }
    return $result;
}
