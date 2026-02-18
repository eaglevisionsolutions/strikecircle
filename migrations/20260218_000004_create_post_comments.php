<?php
// 20260218_000004_create_post_comments.php
// Migration: Create post_comments table for social feed

return [
    'up' => function($pdo) {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS post_comments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                post_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                body TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_post_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_post_comments_post_id (post_id),
                INDEX idx_post_comments_user_id (user_id),
                INDEX idx_post_comments_created_at_id (created_at DESC, id DESC)
            ) CHARACTER SET utf8mb4 ENGINE=InnoDB;
        ');
    },
    'down' => function($pdo) {
        $pdo->exec('DROP TABLE IF EXISTS post_comments;');
    }
];
