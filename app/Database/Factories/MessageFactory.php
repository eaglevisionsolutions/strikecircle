<?php
namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class MessageFactory extends Factory
{
    public function definition(array $overrides = []): array
    {
        $sender_id = $overrides['sender_id'] ?? $this->rand->numberBetween(1, 10);
        $receiver_id = $overrides['receiver_id'] ?? $this->rand->numberBetween(1, 10);
        while ($receiver_id === $sender_id) {
            $receiver_id = $this->rand->numberBetween(1, 10);
        }
        $body = $overrides['body'] ?? $this->rand->sentence(4, 12);
        $created = $overrides['created_at'] ?? $this->rand->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s');
        return [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'body' => $body,
            'created_at' => $created,
        ];
    }

    public function create(array $overrides = []): int
    {
        $data = $this->definition($overrides);
        $stmt = $this->db->prepare('INSERT INTO messages (sender_id, receiver_id, body, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['sender_id'], $data['receiver_id'], $data['body'], $data['created_at']]);
        return (int)$this->db->lastInsertId();
    }
}
