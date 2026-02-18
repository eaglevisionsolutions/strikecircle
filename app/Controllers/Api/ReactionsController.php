<?php
namespace App\Controllers\Api;

use App\Config\db_connect;
use App\Services\ReactionService;
use App\Middleware\Auth;

class ReactionsController {
    public static function require_auth() {
        return Auth::requireAuth();
    }
    public static function handle($method, $postId, $type = null) {
        $pdo = db_connect();
        $user = require_auth();
        $service = new ReactionService($pdo);
        if ($method === 'POST' && !$type) {
            $liked = $service->toggleLike($postId, $user['id']);
            echo json_encode(['success' => true, 'data' => ['liked' => $liked]]);
            exit;
        }
        if ($method === 'DELETE' && $type === 'like') {
            $liked = $service->hasLiked($postId, $user['id']);
            if ($liked) {
                $service->toggleLike($postId, $user['id']);
            }
            echo json_encode(['success' => true, 'data' => ['liked' => false]]);
            exit;
        }
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}
