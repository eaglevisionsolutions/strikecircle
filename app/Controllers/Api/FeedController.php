<?php
// app/Controllers/Api/FeedController.php
require_once __DIR__ . '/../../../Config/database.php';
require_once __DIR__ . '/../../../Services/PostService.php';
require_once __DIR__ . '/../../../Middleware/Auth.php';

class FeedController {
    public static function handle($method) {
        $pdo = db_connect();
        $user = require_auth();
        $service = new PostService($pdo);
        if ($method === 'GET') {
            $cursor = null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            if (isset($_GET['cursor'])) {
                $cursor = json_decode(base64_decode($_GET['cursor']), true);
            }
            $posts = $service->listFeed($user['id'], $cursor, $limit);
            $nextCursor = null;
            if (count($posts) === $limit) {
                $last = end($posts);
                $nextCursor = base64_encode(json_encode([
                    'created_at' => $last['created_at'],
                    'id' => $last['id']
                ]));
            }
            echo json_encode([
                'success' => true,
                'data' => [
                    'posts' => $posts,
                    'next_cursor' => $nextCursor
                ]
            ]);
            exit;
        }
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}
