<?php
// Database connection using PDO
require_once __DIR__ . '/env.php';

function db_connect(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $host    = env('DB_HOST', 'db');
    $port    = env('DB_PORT', '3306');
    $db      = env('DB_NAME', 'strikecircle');
    $user    = env('DB_USER', 'root');
    $pass    = env('DB_PASS', '');
    $charset = env('DB_CHARSET', 'utf8mb4');

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\Throwable $e) {
        $msg = 'Database connection failed: ' . $e->getMessage();

        // CLI migrations: print error
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $msg . PHP_EOL);
            exit(1);
        }

        // Web/API: JSON error response
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'DB_CONN_ERROR',
                'message' => 'Database connection failed.',
            ],
        ]);
        exit;
    }
}