<?php
// app/Database/Factories/ScoreFactory.php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class ScoreFactory extends Factory
{
    public function make(array $overrides = []): array
    {
        $game1 = $this->rand->int(120, 280);
        $game2 = $this->rand->int(120, 280);
        $game3 = $this->rand->int(120, 280);
        $played = (new \DateTimeImmutable('-' . $this->rand->int(1, 14) . ' days'))->format('Y-m-d H:i:s');
        return array_merge([
            'user_id' => $overrides['user_id'] ?? 1,
            'game1' => $game1,
            'game2' => $game2,
            'game3' => $game3,
            'played_at' => $played,
        ], $overrides);
    }

    public function create(array $overrides = []): array
    {
        $data = $this->make($overrides);
        $id = $this->insertAndReturnId('scores', $data);
        $data['id'] = $id;
        return $data;
    }
}
