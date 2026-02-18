<?php
// app/Services/ReactionService.php
require_once __DIR__ . '/../Models/ReactionModel.php';

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
