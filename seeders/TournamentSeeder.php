<?php
// seeders/TournamentSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\TournamentFactory;
use PDO;

final class TournamentSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 6000); // Offset for tournament randomness
        $factory = new TournamentFactory($db, $rand);

        // 2 deterministic tournaments
        $tournamentIds = [];
        for ($i = 0; $i < 2; $i++) {
            $tournamentIds[] = $factory->create();
        }

        // Add all users as participants to each tournament
        $users = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        $partStmt = $db->prepare('INSERT INTO tournament_participants (tournament_id, user_id, joined_at) VALUES (?, ?, ?)');
        foreach ($tournamentIds as $tid) {
            foreach ($users as $u) {
                $partStmt->execute([$tid, $u, (new \DateTimeImmutable('-1 days'))->format('Y-m-d H:i:s')]);
            }
        }

        // Add matches if table exists
        $exists = $db->query("SHOW TABLES LIKE 'tournament_matches'")->fetchColumn();
        if ($exists) {
            $matchStmt = $db->prepare('INSERT INTO tournament_matches (tournament_id, round, created_at) VALUES (?, ?, ?)');
            foreach ($tournamentIds as $tid) {
                $matchStmt->execute([$tid, 1, (new \DateTimeImmutable('-1 days'))->format('Y-m-d H:i:s')]);
            }
        }
    }
}
