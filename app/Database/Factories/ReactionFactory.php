<?php
namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class ReactionFactory extends Factory
{
    protected array $types = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];

    public function definition(array $overrides = []): array
    {
        $post_id = $overrides['post_id'] ?? $this->rand->numberBetween(1, 40);
        $user_id = $overrides['user_id'] ?? $this->rand->numberBetween(1, 10);
        $type = $overrides['type'] ?? $this->types[$this->rand->numberBetween(0, count($this->types) - 1)];
        $created = $overrides['created_at'] ?? $this->rand->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s');
        return [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'type' => $type,
            'created_at' => $created,
        ];
    }

    public function make(array $overrides = []): array
    {
        return $this->definition($overrides);
    }

    public function create(array $overrides = []): array
    {
        $data = $this->make($overrides);
        $stmt = $this->db->prepare('INSERT IGNORE INTO post_reactions (post_id, user_id, type, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['post_id'], $data['user_id'], $data['type'], $data['created_at']]);
        if ($stmt->rowCount() > 0) {
            $data['id'] = (int)$this->db->lastInsertId();
        } else {
            // On duplicate (ignored), fetch existing id for completeness
            $q = $this->db->prepare('SELECT id FROM post_reactions WHERE post_id = ? AND user_id = ? AND type = ? LIMIT 1');
            $q->execute([$data['post_id'], $data['user_id'], $data['type']]);
            $data['id'] = (int)($q->fetchColumn() ?: 0);
        }
        return $data;
    }
}
