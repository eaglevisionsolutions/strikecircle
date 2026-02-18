<?php
// seeders/PostSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class PostSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 1000); // Offset for post randomness
        $factory = new PostFactory($db, $rand);

        // 10 demo posts, one for each user (assuming user IDs 1-10)
        for ($i = 1; $i <= 10; $i++) {
            $factory->create([
                'user_id' => $i,
                'created_at' => (new \DateTimeImmutable('-' . (11 - $i) . ' days'))->format('Y-m-d H:i:s'),
            ]);
        }

        // 30 more random posts
        for ($i = 0; $i < 30; $i++) {
            $factory->create();
        }
    }
}
