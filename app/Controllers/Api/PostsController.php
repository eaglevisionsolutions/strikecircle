<?php
namespace App\Controllers\Api;

use App\Config\db_connect;
use App\Services\PostService;
use App\Middleware\Auth;

class PostsController {
    public static function require_auth() {
        return Auth::requireAuth();
    }
    public static function handle($method, $id = null) {
        $pdo = db_connect();
        $user = require_auth();
        $service = new PostService($pdo);
        if ($method === 'POST' && !$id) {
            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'] ?? null;
            $body = trim($input['body'] ?? '');
            if (!$type || !in_array($type, ['text', 'score'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid type']);
                exit;
            }
            if (strlen($body) < 1 || strlen($body) > 1000) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Body length invalid']);
                exit;
            }
            $scoreData = $type === 'score' ? ($input['score'] ?? null) : null;
            $post = $service->create($user['id'], $type, htmlspecialchars($body), $scoreData);
            echo json_encode(['success' => true, 'data' => $post]);
            exit;
        }
        if ($method === 'PUT' && $id) {
            $input = json_decode(file_get_contents('php://input'), true);
            $body = trim($input['body'] ?? '');
            if (strlen($body) < 1 || strlen($body) > 1000) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Body length invalid']);
                exit;
            }
            $ok = $service->update($id, $user['id'], htmlspecialchars($body));
            echo json_encode(['success' => $ok, $ok ? 'data' : 'error' => $ok ? 'Updated' : 'Not found or not owner']);
            exit;
        }
        if ($method === 'DELETE' && $id) {
            $ok = $service->delete($id, $user['id']);
            echo json_encode(['success' => $ok, $ok ? 'data' : 'error' => $ok ? 'Deleted' : 'Not found or not owner']);
            exit;
        }
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}
