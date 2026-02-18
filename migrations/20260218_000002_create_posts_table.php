<?php
// 20260218_000002_create_posts_table.php

declare(strict_types=1);

use App\Database\Migration;

final class CreatePostsTable extends Migration
{
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type ENUM('text','score') NOT NULL,
                body TEXT NOT NULL,
                score_id INT DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_posts_created_at_id (created_at DESC, id DESC)
            ) CHARACTER SET utf8mb4 ENGINE=InnoDB;
        SQL);
    }
    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS posts;');
    }
}
