<?php
declare(strict_types=1);
namespace App\Services;

use App\Config\DB;
use PDO;

class AuthService {
    public static function uuidv4(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function sanitizeUsername(string $username): string {
        $username = strtolower(trim($username));
        $username = preg_replace('/[^a-z0-9_\.\-]/', '', $username) ?? '';
        return substr($username, 0, 50);
    }

    public static function validatePassword(string $password): bool {
        return strlen($password) >= 8;
    }

    public static function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function getUserAgent(): string {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);
    }

    public static function rateLimitCheck(PDO $pdo, string $ip, string $identifier, int $maxAttempts = 5, int $windowSeconds = 900): bool {
        $stmt = $pdo->prepare('SELECT attempts, last_attempt_at FROM auth_login_attempts WHERE ip = ? AND identifier = ? LIMIT 1');
        $stmt->execute([$ip, $identifier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return true;
        $last = strtotime($row['last_attempt_at']);
        if ($last !== false && (time() - $last) < $windowSeconds && (int)$row['attempts'] >= $maxAttempts) {
            return false;
        }
        return true;
    }

    public static function rateLimitRecord(PDO $pdo, string $ip, string $identifier, bool $success): void {
        $stmt = $pdo->prepare('SELECT id, attempts FROM auth_login_attempts WHERE ip = ? AND identifier = ? LIMIT 1');
        $stmt->execute([$ip, $identifier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($success) {
            if ($row) {
                $pdo->prepare('UPDATE auth_login_attempts SET attempts = 0, last_attempt_at = NOW() WHERE id = ?')->execute([$row['id']]);
            }
            return;
        }
        if ($row) {
            $pdo->prepare('UPDATE auth_login_attempts SET attempts = attempts + 1, last_attempt_at = NOW() WHERE id = ?')->execute([$row['id']]);
        } else {
            $pdo->prepare('INSERT INTO auth_login_attempts (ip, identifier, attempts, last_attempt_at) VALUES (?, ?, 1, NOW())')->execute([$ip, $identifier]);
        }
    }

    public static function findUserByIdentifier(PDO $pdo, string $identifier): array|null {
        $stmt = $pdo->prepare('SELECT id, email, username, password_hash, display_name, avatar_url, is_active FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        return $user;
    }

    public static function createUser(PDO $pdo, string $username, string $email, string $password, ?string $displayName = null, ?string $avatarUrl = null): array {
        $username = self::sanitizeUsername($username);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }
        if (!self::validatePassword($password)) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uuid = self::uuidv4();
        $stmt = $pdo->prepare('INSERT INTO users (uuid, email, username, password_hash, display_name, avatar_url, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
        $stmt->execute([$uuid, $email, $username, $hash, $displayName, $avatarUrl]);
        $id = (int)$pdo->lastInsertId();
        return ['id'=>$id,'uuid'=>$uuid,'email'=>$email,'username'=>$username,'display_name'=>$displayName,'avatar_url'=>$avatarUrl,'is_active'=>1];
    }

    public static function issueAccessToken(array $user, int $ttlSeconds = 900): string {
        $claims = [
            'sub' => (int)$user['id'],
        ];
        return JWTService::encode($claims, $ttlSeconds);
    }

    public static function setRefreshCookies(string $token, int $ttlSeconds, bool $secure): void {
        $domain = \App\Config\Env::get('APP_DOMAIN', 'strikecircle.local');
        // HttpOnly refresh token cookie
        setcookie('refresh_token', $token, [
            'expires' => time() + $ttlSeconds,
            'path' => '/api/v1/auth',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        // Double-submit CSRF token (readable by JS)
        $csrf = bin2hex(random_bytes(16));
        setcookie('refresh_csrf', $csrf, [
            'expires' => time() + $ttlSeconds,
            'path' => '/api/v1/auth',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        // Also expose header for immediate use if needed
        header('X-Refresh-CSRF: ' . $csrf);
    }

    public static function createAndStoreRefreshToken(PDO $pdo, int $userId, int $ttlSeconds): string {
        $raw = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $hash = hash('sha256', $raw);
        $ua = self::getUserAgent();
        $ip = self::getClientIp();
        $expires = (new \DateTimeImmutable('@' . (time() + $ttlSeconds)))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO auth_refresh_tokens (user_id, token_hash, user_agent, ip, expires_at, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $hash, $ua, $ip, $expires]);
        return $raw;
    }

    public static function revokeRefreshToken(PDO $pdo, ?string $rawToken): void {
        if (!$rawToken) return;
        $hash = hash('sha256', $rawToken);
        $pdo->prepare('UPDATE auth_refresh_tokens SET revoked_at = NOW() WHERE token_hash = ? AND revoked_at IS NULL')->execute([$hash]);
    }

    public static function revokeAllForUserAgent(PDO $pdo, int $userId): void {
        $ua = self::getUserAgent();
        $ip = self::getClientIp();
        $pdo->prepare('UPDATE auth_refresh_tokens SET revoked_at = NOW() WHERE user_id = ? AND user_agent = ? AND ip = ? AND revoked_at IS NULL')->execute([$userId, $ua, $ip]);
    }

    public static function validateAndRotateRefresh(PDO $pdo, ?string $rawToken, int $newTtl): array|false {
        if (!$rawToken) return false;
        $hash = hash('sha256', $rawToken);
        $stmt = $pdo->prepare('SELECT * FROM auth_refresh_tokens WHERE token_hash = ? LIMIT 1');
        $stmt->execute([$hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        if ($row['revoked_at'] !== null) return false;
        if (strtotime($row['expires_at']) < time()) return false;
        // rotate: revoke old, issue new
        $pdo->prepare('UPDATE auth_refresh_tokens SET revoked_at = NOW() WHERE id = ?')->execute([$row['id']]);
        $newRaw = self::createAndStoreRefreshToken($pdo, (int)$row['user_id'], $newTtl);
        return ['user_id' => (int)$row['user_id'], 'new_token' => $newRaw];
    }
}
