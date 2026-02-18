<?php
use App\Config\env;
use App\Config\db_connect;

$host = env('DB_HOST', 'db');
$port = env('DB_PORT', '3306');
$name = env('DB_NAME', 'strikecircle');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', 'root');

$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "DB connection OK\n";
    echo "DB={$name} HOST={$host}:{$port}\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "DB connection FAILED: " . $e->getMessage();
}