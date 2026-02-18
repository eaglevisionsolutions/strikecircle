<?php
// seeders/FriendSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\FriendFactory;

final class FriendSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 2000);
        $factory = new FriendFactory($db, $rand);

        // 20 deterministic friend pairs
        $pairs = [];
        while (count($pairs) < 20) {
            $a = $rand->numberBetween(1, 10);
            $b = $rand->numberBetween(1, 10);
            if ($a !== $b && !in_array([$a, $b], $pairs) && !in_array([$b, $a], $pairs)) {
                $pairs[] = [$a, $b];
                $factory->create(['user1_id' => $a, 'user2_id' => $b]);
            }
        }
    }
}
