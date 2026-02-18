<?php
namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class FriendFactory extends Factory
{
    public function definition(array $overrides = []): array
    {
        $user1 = $overrides['user1_id'] ?? $this->rand->numberBetween(1, 10);
        $user2 = $overrides['user2_id'] ?? $this->rand->numberBetween(1, 10);
        while ($user2 === $user1) {
            $user2 = $this->rand->numberBetween(1, 10);
        }
        $created = $overrides['created_at'] ?? $this->rand->dateTimeBetween('-90 days', 'now')->format('Y-m-d H:i:s');
        return [
            'user1_id' => $user1,
            'user2_id' => $user2,
            'created_at' => $created,
        ];
    }

    public function create(array $overrides = []): int
    {
        $data = $this->definition($overrides);
        $stmt = $this->db->prepare('INSERT INTO friends (user1_id, user2_id, created_at) VALUES (?, ?, ?)');
        $stmt->execute([$data['user1_id'], $data['user2_id'], $data['created_at']]);
        return (int)$this->db->lastInsertId();
    }
}
