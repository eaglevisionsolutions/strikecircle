<?php
// seeders/LeagueSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\LeagueFactory;

final class LeagueSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 5000); // Offset for league randomness
        $factory = new LeagueFactory($db, $rand);

        // 3 deterministic leagues
        $leagueIds = [];
        for ($i = 0; $i < 3; $i++) {
            $leagueIds[] = $factory->create();
        }

        // Add all users as members to each league
        $users = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        $memberStmt = $db->prepare('INSERT INTO league_members (league_id, user_id, joined_at) VALUES (?, ?, ?)');
        foreach ($leagueIds as $i => $lid) {
            foreach ($users as $u) {
                $memberStmt->execute([$lid, $u, (new \DateTimeImmutable("-" . ($i+1) . " days"))->format('Y-m-d H:i:s')]);
            }
        }

        // Add league_scores if table exists
        $exists = $db->query("SHOW TABLES LIKE 'league_scores'")->fetchColumn();
        if ($exists) {
            $scoreStmt = $db->prepare('INSERT INTO league_scores (league_id, user_id, score, created_at) VALUES (?, ?, ?, ?)');
            foreach ($leagueIds as $lid) {
                foreach ($users as $u) {
                    $scoreStmt->execute([$lid, $u, rand(150, 300), (new DateTimeImmutable('-1 days'))->format('Y-m-d H:i:s')]);
                }
            }
        }
    }
}
