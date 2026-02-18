<?php
// 20260218_000002_create_posts.php
// Migration: Create posts table for social feed

return [
    'up' => function($pdo) {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                type ENUM(\'text\',\'score\') NOT NULL,
                body TEXT NOT NULL,
                score_id INT UNSIGNED DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_posts_score FOREIGN KEY (score_id) REFERENCES scores(id) ON DELETE SET NULL,
                INDEX idx_posts_created_at_id (created_at DESC, id DESC)
            ) CHARACTER SET utf8mb4 ENGINE=InnoDB;
        ');
    },
    'down' => function($pdo) {
        $pdo->exec('DROP TABLE IF EXISTS posts;');
    }
];
