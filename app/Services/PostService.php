<?php
namespace App\Services;

use App\Models\PostModel;
// use App\Models\ScoreModel; // Uncomment if ScoreModel exists and is namespaced

class PostService {
    private $pdo;
    private $postModel;
    private $scoreModel;
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->postModel = new PostModel($pdo);
        $this->scoreModel = class_exists('ScoreModel') ? new ScoreModel($pdo) : null;
    }

    public function create($userId, $type, $body, $scoreData = null) {
        if ($type === 'score' && $scoreData && $this->scoreModel) {
            $score = $this->scoreModel->create($userId, $scoreData);
            $scoreId = $score['id'] ?? null;
            return $this->postModel->create($userId, $type, $body, $scoreId);
        }
        return $this->postModel->create($userId, $type, $body);
    }

    public function update($id, $userId, $body) {
        return $this->postModel->update($id, $userId, $body);
    }

    public function delete($id, $userId) {
        return $this->postModel->delete($id, $userId);
    }

    public function listFeed($viewerId, $cursor = null, $limit = 10) {
        return $this->postModel->listFeed($viewerId, $cursor, $limit);
    }
}
