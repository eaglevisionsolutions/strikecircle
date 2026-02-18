<?php
namespace App\Models;
// Handles DB operations for post comments

class CommentModel {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function add($postId, $userId, $body) {
        $sql = "INSERT INTO post_comments (post_id, user_id, body) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$postId, $userId, $body]);
        return $this->findById($this->pdo->lastInsertId());
    }

    public function findById($id) {
        $sql = "SELECT * FROM post_comments WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function list($postId, $cursor = null, $limit = 10) {
        $params = [$postId];
        $where = '';
        if ($cursor) {
            $where = 'AND (created_at < ? OR (created_at = ? AND id < ?))';
            $params[] = $cursor['created_at'];
            $params[] = $cursor['created_at'];
            $params[] = $cursor['id'];
        }
        $sql = "
            SELECT c.*, u.username, u.avatar_url
            FROM post_comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ? $where
            ORDER BY c.created_at DESC, c.id DESC
            LIMIT ?
        ";
        $params[] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id, $userId) {
        $sql = "DELETE FROM post_comments WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}
