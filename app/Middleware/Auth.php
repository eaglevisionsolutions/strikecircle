<?php
// Auth middleware for protected routes
require_once __DIR__ . '/../Services/JWTService.php';
require_once __DIR__ . '/../Config/database.php';

class Auth {
    public static function user() {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (preg_match('/Bearer\s(.+)/', $auth, $matches)) {
            $jwt = $matches[1];
            $payload = JWTService::decode($jwt);
            if ($payload && isset($payload['uid'])) {
                $pdo = db_connect();
                $stmt = $pdo->prepare('SELECT id, email, username FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$payload['uid']]);
                $user = $stmt->fetch();
                if ($user) return $user;
            }
        }
        return null;
    }
    public static function requireAuth() {
        $user = self::user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success'=>false,'error'=>['code'=>'UNAUTHORIZED','message'=>'Authentication required.']]);
            exit;
        }
        return $user;
    }
}
