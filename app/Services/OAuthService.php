<?php
declare(strict_types=1);
namespace App\Services;

use App\Config\Env;
use App\Config\DB;
use PDO;

class OAuthService {
    public static function buildGoogleAuthUrl(string $state): string {
        $clientId = Env::get('GOOGLE_CLIENT_ID', '');
        $redirect = urlencode(Env::get('GOOGLE_REDIRECT_URI', ''));
        $scope = urlencode('openid email profile');
        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth'
            . '?response_type=code'
            . '&client_id=' . urlencode($clientId)
            . '&redirect_uri=' . $redirect
            . '&scope=' . $scope
            . '&state=' . urlencode($state)
            . '&access_type=offline'
            . '&prompt=consent';
        return $authUrl;
    }

    public static function buildFacebookAuthUrl(string $state): string {
        $appId = Env::get('FACEBOOK_APP_ID', '');
        $redirect = urlencode(Env::get('FACEBOOK_REDIRECT_URI', ''));
        $scope = urlencode('email,public_profile');
        $authUrl = 'https://www.facebook.com/v19.0/dialog/oauth'
            . '?response_type=code'
            . '&client_id=' . urlencode($appId)
            . '&redirect_uri=' . $redirect
            . '&state=' . urlencode($state)
            . '&scope=' . $scope;
        return $authUrl;
    }

    public static function httpJson(string $url, string $method = 'GET', array $headers = [], array $body = []): array {
        $opts = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
            ]
        ];
        if ($method !== 'GET') {
            $opts['http']['header'] .= ($opts['http']['header'] ? "\r\n" : '') . 'Content-Type: application/x-www-form-urlencoded';
            $opts['http']['content'] = http_build_query($body);
        }
        $ctx = stream_context_create($opts);
        $res = file_get_contents($url, false, $ctx);
        $code = 0;
        if (isset($http_response_header) && preg_match('#\s(\d{3})\s#', $http_response_header[0] ?? '', $m)) {
            $code = (int)$m[1];
        }
        $json = json_decode((string)$res, true) ?: [];
        return ['status' => $code, 'json' => $json, 'raw' => $res];
    }

    public static function googleCallback(string $code): array|false {
        $clientId = Env::get('GOOGLE_CLIENT_ID', '');
        $clientSecret = Env::get('GOOGLE_CLIENT_SECRET', '');
        $redirect = Env::get('GOOGLE_REDIRECT_URI', '');
        $tokenRes = self::httpJson('https://oauth2.googleapis.com/token', 'POST', [], [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect,
        ]);
        if ($tokenRes['status'] !== 200 || empty($tokenRes['json']['access_token'])) return false;
        $accessToken = $tokenRes['json']['access_token'];
        $info = self::httpJson('https://openidconnect.googleapis.com/v1/userinfo', 'GET', [
            'Authorization: Bearer ' . $accessToken
        ]);
        if ($info['status'] !== 200 || empty($info['json']['sub'])) return false;
        return [
            'provider' => 'google',
            'id' => $info['json']['sub'],
            'email' => $info['json']['email'] ?? null,
            'name' => $info['json']['name'] ?? null,
            'picture' => $info['json']['picture'] ?? null,
        ];
    }

    public static function facebookCallback(string $code): array|false {
        $appId = Env::get('FACEBOOK_APP_ID', '');
        $appSecret = Env::get('FACEBOOK_APP_SECRET', '');
        $redirect = Env::get('FACEBOOK_REDIRECT_URI', '');
        $tokenRes = self::httpJson('https://graph.facebook.com/v19.0/oauth/access_token', 'GET', [], [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'code' => $code,
            'redirect_uri' => $redirect,
        ]);
        if ($tokenRes['status'] !== 200 || empty($tokenRes['json']['access_token'])) return false;
        $accessToken = $tokenRes['json']['access_token'];
        $info = self::httpJson('https://graph.facebook.com/me?fields=id,name,email,picture', 'GET', [
            'Authorization: Bearer ' . $accessToken
        ]);
        if ($info['status'] !== 200 || empty($info['json']['id'])) return false;
        return [
            'provider' => 'facebook',
            'id' => $info['json']['id'],
            'email' => $info['json']['email'] ?? null,
            'name' => $info['json']['name'] ?? null,
            'picture' => $info['json']['picture']['data']['url'] ?? null,
        ];
    }

    public static function findOrCreateUserForOAuth(PDO $pdo, array $profile): array {
        // Check linked account
        $stmt = $pdo->prepare('SELECT u.id, u.email, u.username, u.display_name, u.avatar_url, u.is_active FROM auth_oauth_accounts a JOIN users u ON u.id = a.user_id WHERE a.provider = ? AND a.provider_user_id = ? LIMIT 1');
        $stmt->execute([$profile['provider'], $profile['id']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u) return $u;
        // Try matching by email
        if (!empty($profile['email'])) {
            $stmt = $pdo->prepare('SELECT id, email, username, display_name, avatar_url, is_active FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$profile['email']]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($u) {
                // Link account
                $pdo->prepare('INSERT IGNORE INTO auth_oauth_accounts (user_id, provider, provider_user_id, email) VALUES (?, ?, ?, ?)')
                    ->execute([$u['id'], $profile['provider'], $profile['id'], $profile['email']]);
                return $u;
            }
        }
        // Create new user
        $baseUsername = $profile['email'] ? explode('@', $profile['email'])[0] : preg_replace('/\s+/', '', strtolower($profile['name'] ?? 'user'));
        $username = AuthService::sanitizeUsername($baseUsername);
        if ($username === '') $username = 'user';
        // Ensure unique username
        $suffix = 0;
        do {
            $try = $username . ($suffix ? (string)$suffix : '');
            $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$try]);
            $exists = (bool)$stmt->fetchColumn();
            if (!$exists) { $username = $try; break; }
            $suffix++;
        } while (true);
        $email = $profile['email'] ?? (AuthService::uuidv4().'@example.local');
        $created = AuthService::createUser($pdo, $username, $email, bin2hex(random_bytes(12)), $profile['name'] ?? null, $profile['picture'] ?? null);
        // Link account
        $pdo->prepare('INSERT IGNORE INTO auth_oauth_accounts (user_id, provider, provider_user_id, email) VALUES (?, ?, ?, ?)')
            ->execute([$created['id'], $profile['provider'], $profile['id'], $profile['email'] ?? null]);
        return $created;
    }
}
