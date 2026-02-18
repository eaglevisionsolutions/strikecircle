<?php
// app/Models/PostModel.php
// Handles DB operations for posts

class PostModel {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function create($userId, $type, $body, $scoreId = null) {
        $sql = "INSERT INTO posts (user_id, type, body, score_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $type, $body, $scoreId]);
        return $this->findById($this->pdo->lastInsertId());
    }

    public function findById($id) {
        $sql = "SELECT * FROM posts WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $userId, $body) {
        $sql = "UPDATE posts SET body = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$body, $id, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function delete($id, $userId) {
        $sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function listFeed($viewerId, $cursor = null, $limit = 10) {
        $params = [];
        $where = '';
        if ($cursor) {
            $where = 'WHERE (created_at < ? OR (created_at = ? AND id < ?))';
            $params[] = $cursor['created_at'];
            $params[] = $cursor['created_at'];
            $params[] = $cursor['id'];
        }
        $sql = "
            SELECT p.*, u.username, u.avatar_url,
                (SELECT COUNT(*) FROM post_comments c WHERE c.post_id = p.id) AS comment_count,
                (SELECT COUNT(*) FROM post_reactions r WHERE r.post_id = p.id) AS reaction_count,
                (SELECT COUNT(*) FROM post_reactions r WHERE r.post_id = p.id AND r.user_id = ? AND r.type = 'like') AS viewer_has_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            $where
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT ?
        ";
        array_unshift($params, $viewerId);
        $params[] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
