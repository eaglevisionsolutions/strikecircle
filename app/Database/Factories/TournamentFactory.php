<?php
namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class TournamentFactory extends Factory
{
    public function definition(array $overrides = []): array
    {
        $name = $overrides['name'] ?? 'Tournament ' . $this->rand->word() . ' ' . $this->rand->numberBetween(1, 99);
        $location = $overrides['location'] ?? $this->rand->city();
        $date = $overrides['date'] ?? $this->rand->dateTimeBetween('-60 days', '+60 days')->format('Y-m-d');
        $created = $overrides['created_at'] ?? $this->rand->dateTimeBetween('-120 days', 'now')->format('Y-m-d H:i:s');
        return [
            'name' => $name,
            'location' => $location,
            'date' => $date,
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
        $stmt = $this->db->prepare('INSERT INTO tournaments (name, location, date, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['location'], $data['date'], $data['created_at']]);
        $data['id'] = (int)$this->db->lastInsertId();
        return $data;
    }
}
