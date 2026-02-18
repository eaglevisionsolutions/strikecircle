<?php

$host = \App\Config\Env::get('DB_HOST', 'db');
$port = \App\Config\Env::get('DB_PORT', '3306');
$name = \App\Config\Env::get('DB_NAME', 'strikecircle');
$user = \App\Config\Env::get('DB_USER', 'root');
$pass = \App\Config\Env::get('DB_PASS', 'root');

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