<?php
namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class CommentFactory extends Factory
{
    public function definition(array $overrides = []): array
    {
        $post_id = $overrides['post_id'] ?? $this->rand->numberBetween(1, 40);
        $user_id = $overrides['user_id'] ?? $this->rand->numberBetween(1, 10);
        $body = $overrides['body'] ?? $this->rand->sentence(6, 12);
        $created = $overrides['created_at'] ?? $this->rand->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s');
        return [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'body' => $body,
            'created_at' => $created,
        ];
    }

    public function create(array $overrides = []): int
    {
        $data = $this->definition($overrides);
        $stmt = $this->db->prepare('INSERT INTO post_comments (post_id, user_id, body, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['post_id'], $data['user_id'], $data['body'], $data['created_at']]);
        return (int)$this->db->lastInsertId();
    }
}
