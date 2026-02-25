<?php
namespace App\Controllers\Api;

use App\Services\AuthService;
use App\Services\JWTService;
use App\Services\OAuthService;

class AuthController {
    private static function json($status, $payload): void {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($payload);
    }

    private static function requireCsrf(): bool {
        $csrfHeader = $_SERVER['HTTP_X_REFRESH_CSRF'] ?? '';
        $csrfCookie = $_COOKIE['refresh_csrf'] ?? '';
        return $csrfHeader && hash_equals($csrfCookie, $csrfHeader);
    }

    public static function register(): void {
        header('Content-Type: application/json');
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $username = isset($in['username']) ? AuthService::sanitizeUsername((string)$in['username']) : '';
        $email = (string)($in['email'] ?? '');
        $password = (string)($in['password'] ?? '');
        $display = isset($in['display_name']) ? trim((string)$in['display_name']) : null;
        if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !AuthService::validatePassword($password)) {
            self::json(400, ['success'=>false,'error'=>['code'=>'INVALID_INPUT','message'=>'Invalid registration data.']]);
            return;
        }
        $pdo = \App\Config\DB::connect();
        // Uniqueness checks
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn()) {
            self::json(409, ['success'=>false,'error'=>['code'=>'CONFLICT','message'=>'Account already exists.']]);
            return;
        }
        try {
            $user = AuthService::createUser($pdo, $username, $email, $password, $display, null);
        } catch (\Throwable $e) {
            self::json(400, ['success'=>false,'error'=>['code'=>'INVALID_INPUT','message'=>$e->getMessage()]]);
            return;
        }
        $access = AuthService::issueAccessToken(['id'=>$user['id']], 900);
        $remember = (bool)($in['remember_me'] ?? false);
        $ttl = $remember ? 60*60*24*30 : 60*60*24;
        $refreshRaw = AuthService::createAndStoreRefreshToken($pdo, (int)$user['id'], $ttl);
        $secure = (\App\Config\Env::get('APP_ENV', 'local') !== 'local');
        AuthService::setRefreshCookies($refreshRaw, $ttl, $secure);
        self::json(201, ['success'=>true,'data'=>[
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'display_name' => $user['display_name'] ?? null,
                'avatar_url' => $user['avatar_url'] ?? null,
            ],
            'access_token' => $access,
            'expires_in' => 900,
        ]]);
    }

    public static function login(): void {
        header('Content-Type: application/json');
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $identifier = trim((string)($in['identifier'] ?? $in['email'] ?? ''));
        $password = (string)($in['password'] ?? '');
        $remember = (bool)($in['remember_me'] ?? false);
        if ($identifier === '' || $password === '') {
            self::json(400, ['success'=>false,'error'=>['code'=>'INVALID_INPUT','message'=>'Invalid credentials.']]);
            return;
        }
        $pdo = \App\Config\DB::connect();
        $ip = AuthService::getClientIp();
        if (!AuthService::rateLimitCheck($pdo, $ip, $identifier)) {
            self::json(429, ['success'=>false,'error'=>['code'=>'RATE_LIMIT','message'=>'Too many attempts. Try later.']]);
            return;
        }
        $user = AuthService::findUserByIdentifier($pdo, $identifier);
        $ok = $user && !empty($user['password_hash']) && password_verify($password, $user['password_hash']) && (int)$user['is_active'] === 1;
        AuthService::rateLimitRecord($pdo, $ip, $identifier, $ok);
        if (!$ok) {
            self::json(401, ['success'=>false,'error'=>['code'=>'AUTH_FAILED','message'=>'Invalid credentials.']]);
            return;
        }
        $access = AuthService::issueAccessToken($user, 900);
        $ttl = $remember ? 60*60*24*30 : 60*60*24; // 30d vs 1d
        $refreshRaw = AuthService::createAndStoreRefreshToken($pdo, (int)$user['id'], $ttl);
        $secure = (\App\Config\Env::get('APP_ENV', 'local') !== 'local');
        AuthService::setRefreshCookies($refreshRaw, $ttl, $secure);
        self::json(200, ['success'=>true,'data'=>[
            'access_token' => $access,
            'expires_in' => 900,
            'user' => [
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'display_name' => $user['display_name'] ?? null,
                'avatar_url' => $user['avatar_url'] ?? null,
            ],
        ]]);
    }

    public static function refresh(): void {
        header('Content-Type: application/json');
        if (!self::requireCsrf()) {
            self::json(403, ['success'=>false,'error'=>['code'=>'CSRF','message'=>'Invalid refresh CSRF token.']]);
            return;
        }
        $raw = $_COOKIE['refresh_token'] ?? '';
        $pdo = \App\Config\DB::connect();
        $rotated = AuthService::validateAndRotateRefresh($pdo, $raw, 60*60*24); // default rotation issues 1d lifetime
        if (!$rotated) {
            self::json(401, ['success'=>false,'error'=>['code'=>'INVALID_TOKEN','message'=>'Refresh token invalid.']]);
            return;
        }
        // Set new cookie
        $secure = (\App\Config\Env::get('APP_ENV', 'local') !== 'local');
        AuthService::setRefreshCookies($rotated['new_token'], 60*60*24, $secure);
        // Issue new access token
        $stmt = $pdo->prepare('SELECT id, email, username, display_name, avatar_url FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$rotated['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            self::json(401, ['success'=>false,'error'=>['code'=>'INVALID_TOKEN','message'=>'User not found.']]);
            return;
        }
        $access = AuthService::issueAccessToken(['id' => (int)$user['id']], 900);
        self::json(200, ['success'=>true,'data'=>[
            'access_token' => $access,
            'expires_in' => 900,
            'user' => $user,
        ]]);
    }

    public static function logout(): void {
        header('Content-Type: application/json');
        if (!self::requireCsrf()) {
            self::json(403, ['success'=>false,'error'=>['code'=>'CSRF','message'=>'Invalid refresh CSRF token.']]);
            return;
        }
        $raw = $_COOKIE['refresh_token'] ?? '';
        $pdo = \App\Config\DB::connect();
        if ($raw) {
            AuthService::revokeRefreshToken($pdo, $raw);
        }
        // Try to revoke all for this user-agent if access token provided
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (preg_match('/Bearer\s(.+)/', $auth, $m)) {
            $payload = JWTService::decode($m[1]);
            if ($payload && isset($payload['sub'])) {
                AuthService::revokeAllForUserAgent($pdo, (int)$payload['sub']);
            }
        }
        // Clear cookies
        $domain = \App\Config\Env::get('APP_DOMAIN', 'strikecircle.local');
        foreach (['refresh_token','refresh_csrf'] as $c) {
            setcookie($c, '', [
                'expires' => time() - 3600,
                'path' => '/api/v1/auth',
                'domain' => $domain,
                'secure' => (\App\Config\Env::get('APP_ENV', 'local') !== 'local'),
                'httponly' => ($c === 'refresh_token'),
                'samesite' => 'Lax',
            ]);
        }
        self::json(200, ['success'=>true,'data'=>[]]);
    }

    public static function me(): void {
        header('Content-Type: application/json');
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (!preg_match('/Bearer\s(.+)/', $auth, $m)) {
            self::json(401, ['success'=>false,'error'=>['code'=>'UNAUTHORIZED','message'=>'Missing token.']]);
            return;
        }
        $payload = JWTService::decode($m[1]);
        if (!$payload || !isset($payload['sub'])) {
            self::json(401, ['success'=>false,'error'=>['code'=>'UNAUTHORIZED','message'=>'Invalid token.']]);
            return;
        }
        $pdo = \App\Config\DB::connect();
        $stmt = $pdo->prepare('SELECT id, email, username, display_name, avatar_url FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$payload['sub']]);
        $user = $stmt->fetch();
        if (!$user) {
            self::json(404, ['success'=>false,'error'=>['code'=>'NOT_FOUND','message'=>'User not found.']]);
            return;
        }
        self::json(200, ['success'=>true,'data'=>['user'=>$user]]);
    }

    public static function oauthStart(string $provider): void {
        $state = bin2hex(random_bytes(16));
        $domain = \App\Config\Env::get('APP_DOMAIN', 'strikecircle.local');
        setcookie('oauth_state', $state, [
            'expires' => time() + 600,
            'path' => '/api/v1/auth',
            'domain' => $domain,
            'secure' => (\App\Config\Env::get('APP_ENV', 'local') !== 'local'),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        if ($provider === 'google') {
            $url = OAuthService::buildGoogleAuthUrl($state);
        } elseif ($provider === 'facebook') {
            $url = OAuthService::buildFacebookAuthUrl($state);
        } else {
            self::json(400, ['success'=>false,'error'=>['code'=>'INVALID_PROVIDER','message'=>'Unsupported provider.']]);
            return;
        }
        header('Location: ' . $url, true, 302);
        exit;
    }

    public static function oauthCallback(string $provider): void {
        $qs = $_GET;
        $state = (string)($qs['state'] ?? '');
        $expected = $_COOKIE['oauth_state'] ?? '';
        if (!$state || !$expected || !hash_equals($expected, $state)) {
            self::json(400, ['success'=>false,'error'=>['code'=>'STATE_MISMATCH','message'=>'Invalid state.']]);
            return;
        }
        $code = (string)($qs['code'] ?? '');
        if (!$code) {
            self::json(400, ['success'=>false,'error'=>['code'=>'MISSING_CODE','message'=>'Missing code.']]);
            return;
        }
        if ($provider === 'google') {
            $profile = OAuthService::googleCallback($code);
        } elseif ($provider === 'facebook') {
            $profile = OAuthService::facebookCallback($code);
        } else {
            self::json(400, ['success'=>false,'error'=>['code'=>'INVALID_PROVIDER','message'=>'Unsupported provider.']]);
            return;
        }
        if (!$profile) {
            self::json(401, ['success'=>false,'error'=>['code'=>'OAUTH_FAILED','message'=>'OAuth exchange failed.']]);
            return;
        }
        $pdo = \App\Config\DB::connect();
        $user = OAuthService::findOrCreateUserForOAuth($pdo, $profile);
        // Issue tokens and set refresh cookie
        $access = AuthService::issueAccessToken(['id'=>(int)$user['id']], 900);
        $ttl = 60*60*24*30; // Social login uses 30 days by default
        $refreshRaw = AuthService::createAndStoreRefreshToken($pdo, (int)$user['id'], $ttl);
        $secure = (\App\Config\Env::get('APP_ENV', 'local') !== 'local');
        AuthService::setRefreshCookies($refreshRaw, $ttl, $secure);
        // Redirect to frontend callback page; app will exchange via refresh
        header('Location: /auth/callback.html#ok=1', true, 302);
        exit;
    }
}

