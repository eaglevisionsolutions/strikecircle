<?php
// seeders/ReactionSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\ReactionFactory;
use PDO;

final class ReactionSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 3000);
        $factory = new ReactionFactory($db, $rand);

        // 60 deterministic reactions
        for ($i = 0; $i < 60; $i++) {
            $factory->create();
        }
    }
}
