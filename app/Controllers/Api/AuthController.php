<?php
// /api/v1/auth/login endpoint
require_once __DIR__ . '/../../../Services/JWTService.php';
require_once __DIR__ . '/../../../Config/database.php';

class AuthController {
    public static function login() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>['code'=>'INVALID_INPUT','message'=>'Email and password required.']]);
            return;
        }
        $pdo = db_connect();
        $stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$input['email']]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($input['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['success'=>false,'error'=>['code'=>'AUTH_FAILED','message'=>'Invalid credentials.']]);
            return;
        }
        $payload = [
            'uid' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
        ];
        $accessToken = JWTService::encode($payload, 900); // 15 min
        $refreshToken = JWTService::encode($payload, 604800); // 7 days
        setcookie('refresh_token', $refreshToken, [
            'expires' => time() + 604800,
            'httponly' => true,
            'samesite' => 'Lax',
            'path' => '/api/v1/auth/refresh',
        ]);
        echo json_encode(['success'=>true,'data'=>['access_token'=>$accessToken]]);
    }
}
