<?php
// seeders/TournamentSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class TournamentSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $users = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        if (count($users) < 3) throw new Exception('Not enough users to seed tournaments');

        $tournaments = [
            ['Spring Open', 'single', '-2 days'],
            ['Doubles Showdown', 'doubles', '-1 days'],
        ];
        $stmt = $db->prepare('INSERT INTO tournaments (name, format, created_at) VALUES (?, ?, ?)');
        $tournamentIds = [];
        foreach ($tournaments as [$name, $format, $when]) {
            $created = (new DateTimeImmutable($when))->format('Y-m-d H:i:s');
            $stmt->execute([$name, $format, $created]);
            $tournamentIds[] = $db->lastInsertId();
        }
        // Add participants
        $partStmt = $db->prepare('INSERT INTO tournament_participants (tournament_id, user_id, joined_at) VALUES (?, ?, ?)');
        foreach ($tournamentIds as $tid) {
            foreach ($users as $u) {
                $partStmt->execute([$tid, $u, (new DateTimeImmutable('-1 days'))->format('Y-m-d H:i:s')]);
            }
        }
        // Add matches if table exists
        $exists = $db->query("SHOW TABLES LIKE 'tournament_matches'")->fetchColumn();
        if ($exists) {
            $matchStmt = $db->prepare('INSERT INTO tournament_matches (tournament_id, round, created_at) VALUES (?, ?, ?)');
            foreach ($tournamentIds as $tid) {
                $matchStmt->execute([$tid, 1, (new DateTimeImmutable('-1 days'))->format('Y-m-d H:i:s')]);
            }
        }
    }
}
