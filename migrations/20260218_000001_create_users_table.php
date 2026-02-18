<?php
// 20260218_000001_create_users_table.php

declare(strict_types=1);

use App\Database\Migration;
use PDO;

final class CreateUsersTable extends Migration
{
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }
    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS users;');
    }
}
