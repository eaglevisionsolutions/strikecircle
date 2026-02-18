<?php
// seeders/LeagueSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class LeagueSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $users = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        if (count($users) < 3) throw new Exception('Not enough users to seed leagues');

        $leagues = [
            ['Online Masters', 'online', '-3 days'],
            ['City Bowlers', 'local', '-2 days'],
            ['Pin Crushers', 'local', '-1 days'],
        ];
        $stmt = $db->prepare('INSERT INTO leagues (name, type, created_at) VALUES (?, ?, ?)');
        $leagueIds = [];
        foreach ($leagues as [$name, $type, $when]) {
            $created = (new DateTimeImmutable($when))->format('Y-m-d H:i:s');
            $stmt->execute([$name, $type, $created]);
            $leagueIds[] = $db->lastInsertId();
        }
        // Add members
        $memberStmt = $db->prepare('INSERT INTO league_members (league_id, user_id, joined_at) VALUES (?, ?, ?)');
        foreach ($leagueIds as $i => $lid) {
            foreach ($users as $u) {
                $memberStmt->execute([$lid, $u, (new DateTimeImmutable("-" . ($i+1) . " days"))->format('Y-m-d H:i:s')]);
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
