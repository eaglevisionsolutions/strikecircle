<?php
// seeders/UserSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\UserFactory;

final class UserSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed);
        $factory = new UserFactory($db, $rand);

        // Demo and admin users with fixed credentials
        $exists = function (string $email) use ($db): bool {
            $stmt = $db->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            return (bool)$stmt->fetchColumn();
        };

        if (!$exists('demo@strikecircle.local')) {
            $factory->create([
                'email' => 'demo@strikecircle.local',
                'username' => 'demo',
                'display_name' => 'Demo User',
                'password' => password_hash('DemoPass123!', PASSWORD_DEFAULT),
                'avatar_url' => 'https://i.pravatar.cc/150?u=demo',
                'bio' => 'Bowling is my passion.',
                'location' => 'Los Angeles',
                'created_at' => (new \DateTimeImmutable('-90 days'))->format('Y-m-d H:i:s'),
            ]);
        }
        if (!$exists('admin@strikecircle.local')) {
            $factory->create([
                'email' => 'admin@strikecircle.local',
                'username' => 'admin',
                'display_name' => 'Admin User',
                'password' => password_hash('AdminPass123!', PASSWORD_DEFAULT),
                'avatar_url' => 'https://i.pravatar.cc/150?u=admin',
                'bio' => 'I run the show.',
                'location' => 'New York',
                'created_at' => (new \DateTimeImmutable('-88 days'))->format('Y-m-d H:i:s'),
            ]);
        }

        // 8 more realistic users
        for ($i = 0; $i < 8; $i++) {
            $factory->create();
        }
    }
}
