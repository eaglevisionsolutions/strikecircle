<?php
namespace App\Controllers\Api;

use App\Config\db_connect;
use App\Services\CommentService;
use App\Middleware\Auth;

class CommentsController {
    public static function require_auth() {
        return Auth::requireAuth();
    }
    public static function handle($method, $postId = null, $commentId = null) {
        $pdo = db_connect();
        $user = require_auth();
        $service = new CommentService($pdo);
        if ($method === 'GET' && $postId) {
            $cursor = null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($_GET['cursor'])) {
                $cursor = json_decode(base64_decode($_GET['cursor']), true);
            }
            $comments = $service->list($postId, $cursor, $limit);
            $nextCursor = null;
            if (count($comments) === $limit) {
                $last = end($comments);
                $nextCursor = base64_encode(json_encode([
                    'created_at' => $last['created_at'],
                    'id' => $last['id']
                ]));
            }
            echo json_encode([
                'success' => true,
                'data' => [
                    'comments' => $comments,
                    'next_cursor' => $nextCursor
                ]
            ]);
            exit;
        }
        if ($method === 'POST' && $postId) {
            $input = json_decode(file_get_contents('php://input'), true);
            $body = trim($input['body'] ?? '');
            if (strlen($body) < 1 || strlen($body) > 500) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Body length invalid']);
                exit;
            }
            $comment = $service->add($postId, $user['id'], htmlspecialchars($body));
            echo json_encode(['success' => true, 'data' => $comment]);
            exit;
        }
        if ($method === 'DELETE' && $commentId) {
            $ok = $service->delete($commentId, $user['id']);
            echo json_encode(['success' => $ok, $ok ? 'data' : 'error' => $ok ? 'Deleted' : 'Not found or not owner']);
            exit;
        }
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}
