<?php
namespace App\Config;

require_once __DIR__ . '/env.php';

use PDO;

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

    <?php
    namespace App\Config;
    declare(strict_types=1);

    use PDO;
    use PDOException;

    /**
     * Returns a PDO connection using Env config
     * Usage: DB::connect()
     */

    class DB {
        public static function connect(): PDO
        {
            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $db   = Env::get('DB_DATABASE', 'strikecircle');
            $user = Env::get('DB_USERNAME', 'root');
            $pass = Env::get('DB_PASSWORD', '');
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