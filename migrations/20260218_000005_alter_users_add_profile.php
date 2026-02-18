<?php
// 20260218_000005_alter_users_add_profile.php

declare(strict_types=1);

use App\Database\Migration;

final class AlterUsersAddProfile extends Migration
{
    public function up(PDO $db): void
    {
        // Add profile-related columns if not present (compatible with MySQL/MariaDB)
        if (!$this->columnExists($db, 'users', 'display_name')) {
            $db->exec("ALTER TABLE users ADD COLUMN display_name VARCHAR(100) NULL AFTER username");
        }
        if (!$this->columnExists($db, 'users', 'avatar_url')) {
            $db->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL AFTER display_name");
        }
        if (!$this->columnExists($db, 'users', 'bio')) {
            $db->exec("ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER avatar_url");
        }
        if (!$this->columnExists($db, 'users', 'location')) {
            $db->exec("ALTER TABLE users ADD COLUMN location VARCHAR(100) NULL AFTER bio");
        }
    }

    public function down(PDO $db): void
    {
        // Drop columns if they exist
        foreach (['location','bio','avatar_url','display_name'] as $col) {
            if ($this->columnExists($db, 'users', $col)) {
                $db->exec("ALTER TABLE users DROP COLUMN `$col`");
            }
        }
    }

    private function columnExists(PDO $db, string $table, string $column): bool
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return false;
        }
        $columnQ = $db->quote($column);
        $sql = "SHOW COLUMNS FROM `{$table}` LIKE {$columnQ}";
        $stmt = $db->query($sql);
        return $stmt !== false && (bool)$stmt->fetch();
    }
}
