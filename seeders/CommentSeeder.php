<?php
// seeders/CommentSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\CommentFactory;

final class CommentSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 4000); // Offset for comment randomness
        $factory = new CommentFactory($db, $rand);

        // 50 deterministic comments
        for ($i = 0; $i < 50; $i++) {
            $factory->create();
        }
    }
}
