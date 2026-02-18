<?php
declare(strict_types=1);
namespace App\Config;


use PDO;
use PDOException;

/**
 * Returns a PDO connection using Env config
 * Usage: DB::connect()
 */
class DB {
    public static function connect(): PDO
    {
        // Prefer new-style keys, fallback to legacy ones, with Docker-friendly defaults
        $host = Env::get('DB_HOST', 'db');
        $port = Env::get('DB_PORT', '3306');
        $db   = Env::get('DB_DATABASE', Env::get('DB_NAME', 'strikecircle'));
        $user = Env::get('DB_USERNAME', Env::get('DB_USER', 'root'));
        $pass = Env::get('DB_PASSWORD', Env::get('DB_PASS', ''));
        $charset = Env::get('DB_CHARSET', 'utf8mb4');
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode());
        }
    }
}