<?php
// seeders/ScoreSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\ScoreFactory;
use PDO;

final class ScoreSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 8000); // Offset for score randomness
        $factory = new ScoreFactory($db, $rand);

        // 30 deterministic scores
        for ($i = 0; $i < 30; $i++) {
            $factory->create();
        }
    }
}
