<?php
// Database connection using PDO
require_once __DIR__ . '/env.php';

function db_connect() {
    static $pdo = null;
    if ($pdo) return $pdo;
    $host = env('DB_HOST', 'localhost');
    $db   = env('DB_NAME', 'strikecircle');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>['code'=>'DB_CONN_ERROR','message'=>'Database connection failed.']]);
        exit;
    }
    return $pdo;
}
