<?php
// _create_auth_tables.php
// Run with: php migrations/_create_auth_tables.php
declare(strict_types=1);

require_once __DIR__ . '/../app/autoload.php';

use App\Config\DB;

function ensureTable(PDO $db, string $sql): void {
    $db->exec($sql);
}

function columnExists(PDO $db, string $table, string $column): bool {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return false;
    }
    $stmt = $db->query("SHOW COLUMNS FROM `{$table}` LIKE " . $db->quote($column));
    return $stmt !== false && (bool)$stmt->fetch();
}

try {
    $db = DB::connect();
    $db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Ensure users table has required columns
    if (columnExists($db, 'users', 'password') && !columnExists($db, 'users', 'password_hash')) {
        $db->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL AFTER password");
        $db->exec("UPDATE users SET password_hash = password WHERE password IS NOT NULL AND (password_hash IS NULL OR password_hash = '')");
    }
    if (!columnExists($db, 'users', 'uuid')) {
        $db->exec("ALTER TABLE users ADD COLUMN uuid CHAR(36) NULL AFTER id");
        $db->exec("UPDATE users SET uuid = (SELECT UUID()) WHERE uuid IS NULL");
        $db->exec("ALTER TABLE users ADD UNIQUE KEY `users_uuid_unique` (uuid)");
    }
    if (!columnExists($db, 'users', 'is_active')) {
        $db->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER avatar_url");
    }
    if (!columnExists($db, 'users', 'updated_at')) {
        $db->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    }

    // auth_refresh_tokens
    ensureTable($db, <<<SQL
        CREATE TABLE IF NOT EXISTS auth_refresh_tokens (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            token_hash CHAR(64) NOT NULL,
            user_agent VARCHAR(255) NULL,
            ip VARCHAR(45) NULL,
            expires_at DATETIME NOT NULL,
            revoked_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_token (token_hash),
            CONSTRAINT fk_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    SQL);

    // auth_oauth_accounts
    ensureTable($db, <<<SQL
        CREATE TABLE IF NOT EXISTS auth_oauth_accounts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            provider ENUM('google','facebook') NOT NULL,
            provider_user_id VARCHAR(191) NOT NULL,
            email VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_provider_user (provider, provider_user_id),
            INDEX idx_user (user_id),
            CONSTRAINT fk_oauth_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    SQL);

    // auth_login_attempts (rate limiting)
    ensureTable($db, <<<SQL
        CREATE TABLE IF NOT EXISTS auth_login_attempts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            identifier VARCHAR(191) NOT NULL,
            attempts INT UNSIGNED NOT NULL DEFAULT 0,
            last_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ip_identifier (ip, identifier),
            INDEX idx_last_attempt (last_attempt_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    SQL);

    echo "Auth tables ensured.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration failed: ' . $e->getMessage() . "\n");
    exit(1);
}
