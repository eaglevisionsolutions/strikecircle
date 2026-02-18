<?php
// app/Database/Factories/PostFactory.php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class PostFactory extends Factory
{
    private static array $texts = [
        'Bowled a new high score!', 'League night was awesome!', 'Anyone up for a game this weekend?',
        'Just joined a new league!', 'Practicing my hook shot.', 'Almost got a turkey!',
        'Bowling with friends is the best.', 'Trying out a new ball.', 'Personal best today!',
        'Who else is playing in the tournament?', 'Ready for the finals!', 'Strike after strike!',
        'Spare game on point.', 'Bowling is life.', 'Let’s roll!', 'Pin crusher!',
        'Chasing that perfect game.', 'Lane conditions were tough.', 'Great match tonight!',
        'Celebrating with my team!'
    ];

    public function make(array $overrides = []): array
    {
        $type = $overrides['type'] ?? $this->rand->pick(['text', 'score']);
        $body = $type === 'text' ? $this->rand->pick(self::$texts) : 'Score post';
        $created = (new \DateTimeImmutable('-' . $this->rand->int(1, 14) . ' days'))->format('Y-m-d H:i:s');
        return array_merge([
            'user_id' => $overrides['user_id'] ?? 1,
            'type' => $type,
            'body' => $body,
            'score_id' => $overrides['score_id'] ?? null,
            'created_at' => $created,
        ], $overrides);
    }

    public function create(array $overrides = []): array
    {
        $data = $this->make($overrides);
        $id = $this->insertAndReturnId('posts', $data);
        $data['id'] = $id;
        return $data;
    }
}
