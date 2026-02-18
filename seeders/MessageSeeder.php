<?php
// seeders/MessageSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class MessageSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $users = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        if (count($users) < 2) throw new Exception('Not enough users to seed messages');

        // Create threads
        $threadStmt = $db->prepare('INSERT INTO message_threads (name, created_at) VALUES (?, ?)');
        $threads = [
            ['General Chat', '-2 days'],
            ['League Banter', '-1 days'],
        ];
        $threadIds = [];
        foreach ($threads as [$name, $when]) {
            $created = (new DateTimeImmutable($when))->format('Y-m-d H:i:s');
            $threadStmt->execute([$name, $created]);
            $threadIds[] = $db->lastInsertId();
        }
        // Add members
        $memberStmt = $db->prepare('INSERT INTO message_thread_members (thread_id, user_id, joined_at) VALUES (?, ?, ?)');
        foreach ($threadIds as $tid) {
            foreach ($users as $u) {
                $memberStmt->execute([$tid, $u, (new DateTimeImmutable('-1 days'))->format('Y-m-d H:i:s')]);
            }
        }
        // Add messages
        $msgStmt = $db->prepare('INSERT INTO messages (thread_id, user_id, body, created_at) VALUES (?, ?, ?, ?)');
        for ($i = 0; $i < 10; $i++) {
            $tid = $threadIds[$i % count($threadIds)];
            $uid = $users[$i % count($users)];
            $body = "Message $i";
            $created = (new DateTimeImmutable("-" . ($i%3) . " days"))->format('Y-m-d H:i:s');
            $msgStmt->execute([$tid, $uid, $body, $created]);
        }
    }
}
