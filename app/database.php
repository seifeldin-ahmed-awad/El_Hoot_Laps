<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function database(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $settings = config_database();
    $isLocalClient = PHP_SAPI === 'cli' || in_array(
        $_SERVER['REMOTE_ADDR'] ?? '',
        ['127.0.0.1', '::1'],
        true
    );

    if (!$isLocalClient) {
        foreach ($settings as $value) {
            if ($value === false || $value === '') {
                throw new RuntimeException(
                    'Database environment variables must be configured for non-local access.'
                );
            }
        }
    }

    $host = $settings['host'] ?: '127.0.0.1';
    $port = $settings['port'] ?: '3306';
    $name = $settings['name'] ?: 'breachlab';
    $username = $settings['username'] ?: 'root';
    $password = $settings['password'] === false ? '' : $settings['password'];

    $connection = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $connection;
}
