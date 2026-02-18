<?php
namespace App\Services;

use App\Models\CommentModel;

class CommentService {
    private $commentModel;
    public function __construct($pdo) {
        $this->commentModel = new CommentModel($pdo);
    }

    public function add($postId, $userId, $body) {
        return $this->commentModel->add($postId, $userId, $body);
    }

    public function list($postId, $cursor = null, $limit = 10) {
        return $this->commentModel->list($postId, $cursor, $limit);
    }

    public function delete($id, $userId) {
        return $this->commentModel->delete($id, $userId);
    }
}
