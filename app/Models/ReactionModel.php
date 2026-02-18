<?php
namespace App\Models;
// Handles DB operations for post reactions

class ReactionModel {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function toggleLike($postId, $userId) {
        // Try to insert, if duplicate, delete (toggle)
        $sql = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (?, ?, 'like')";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$postId, $userId]);
            return true; // Liked
        } catch (PDOException $e) {
            // Duplicate: remove
            $sql = "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ? AND type = 'like'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$postId, $userId]);
            return false; // Unliked
        }
    }

    public function hasLiked($postId, $userId) {
        $sql = "SELECT 1 FROM post_reactions WHERE post_id = ? AND user_id = ? AND type = 'like'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$postId, $userId]);
        return (bool)$stmt->fetchColumn();
    }
}
