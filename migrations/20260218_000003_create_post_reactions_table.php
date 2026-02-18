<?php
// 20260218_000003_create_post_reactions_table.php

declare(strict_types=1);

use App\Database\Migration;

final class CreatePostReactionsTable extends Migration
{
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS post_reactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                user_id INT NOT NULL,
                type ENUM('like', 'love', 'laugh', 'wow', 'sad', 'angry') NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_post_reactions_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_reactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY uniq_post_user_type (post_id, user_id, type),
                INDEX idx_post_reactions_post_id (post_id),
                INDEX idx_post_reactions_user_id (user_id)
            ) CHARACTER SET utf8mb4 ENGINE=InnoDB;
        SQL);
    }
    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS post_reactions;');
    }
}
