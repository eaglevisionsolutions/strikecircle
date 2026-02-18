<?php
namespace App\Services;

use App\Models\ReactionModel;

class ReactionService {
    private $reactionModel;
    public function __construct($pdo) {
        $this->reactionModel = new ReactionModel($pdo);
    }

    public function toggleLike($postId, $userId) {
        return $this->reactionModel->toggleLike($postId, $userId);
    }

    public function hasLiked($postId, $userId) {
        return $this->reactionModel->hasLiked($postId, $userId);
    }
}
