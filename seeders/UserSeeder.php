<?php
// seeders/UserSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class UserSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $users = [
            ['demo@strikecircle.local', 'demo', 'DemoPass123!'],
            ['alice@strikecircle.local', 'alice', 'AlicePass!'],
            ['bob@strikecircle.local', 'bob', 'BobPass!'],
            ['carol@strikecircle.local', 'carol', 'CarolPass!'],
            ['dave@strikecircle.local', 'dave', 'DavePass!'],
        ];
        $stmt = $db->prepare('INSERT INTO users (email, username, password, created_at) VALUES (?, ?, ?, ?)');
        $now = (new DateTimeImmutable('-5 days'))->format('Y-m-d H:i:s');
        foreach ($users as $i => [$email, $username, $pw]) {
            $created = (new DateTimeImmutable("-$i days"))->format('Y-m-d H:i:s');
            $stmt->execute([$email, $username, password_hash($pw, PASSWORD_DEFAULT), $created]);
        }
    }
}
