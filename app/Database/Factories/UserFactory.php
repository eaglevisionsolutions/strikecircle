<?php
// app/Database/Factories/UserFactory.php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Database\Faker\SeededRandom;
use PDO;

class UserFactory extends Factory
{
    private static array $firstNames = [
        'Alex', 'Jamie', 'Taylor', 'Jordan', 'Morgan', 'Casey', 'Riley', 'Skyler', 'Avery', 'Peyton',
        'Drew', 'Cameron', 'Quinn', 'Reese', 'Rowan', 'Sawyer', 'Emerson', 'Finley', 'Harper', 'Parker'
    ];
    private static array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Martinez', 'Lee',
        'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Green', 'Baker', 'Adams'
    ];
    private static array $cities = [
        'Los Angeles', 'New York', 'Chicago', 'Houston', 'Miami', 'Seattle', 'Denver', 'Boston', 'San Francisco', 'Dallas'
    ];
    private static array $bios = [
        'Bowling is my passion.', 'Chasing 300.', 'Queen of the lanes.', 'Rolling since 2000.',
        'Spare specialist.', 'Three strikes in a row!', 'Hero of the lanes.', 'Pins fear me.',
        'Practice makes perfect.', 'League night every week.'
    ];

    public function make(array $overrides = []): array
    {
        $first = $this->rand->pick(self::$firstNames);
        $last = $this->rand->pick(self::$lastNames);
        $display = "$first $last";
        $username = $this->uniqueUsername(strtolower($first . $last));
        $email = $this->uniqueEmail(strtolower($first . $last));
        $avatar = 'https://i.pravatar.cc/150?u=' . $username;
        $bio = $this->rand->pick(self::$bios);
        $location = $this->rand->pick(self::$cities);
        $created = (new \DateTimeImmutable('-' . $this->rand->int(1, 90) . ' days'))->format('Y-m-d H:i:s');
        $password = password_hash('TestPass123!', PASSWORD_DEFAULT);
        return array_merge([
            'email' => $email,
            'username' => $username,
            'password' => $password, // legacy column
            'password_hash' => $password, // preferred column
            'display_name' => $display,
            'avatar_url' => $avatar,
            'bio' => $bio,
            'location' => $location,
            'created_at' => $created,
        ], $overrides);
    }

    public function create(array $overrides = []): array
    {
        $data = $this->make($overrides);
        $id = $this->insertAndReturnId('users', $data);
        $data['id'] = $id;
        return $data;
    }
}
