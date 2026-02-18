<?php
// seeders/PostSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class PostSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        // Get user ids
        $users = $db->query('SELECT id FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        if (count($users) < 3) throw new Exception('Not enough users to seed posts');

        // Insert scores
        $scoreStmt = $db->prepare('INSERT INTO scores (user_id, value, created_at) VALUES (?, ?, ?)');
        $scoreRows = [];
        $scoreData = [
            [1, 245, '-4 days'],
            [2, 210, '-3 days'],
            [3, 298, '-2 days'],
            [1, 180, '-1 days'],
            [2, 222, 'now'],
            [3, 199, 'now'],
        ];
        foreach ($scoreData as [$u, $val, $when]) {
            $created = (new DateTimeImmutable($when))->format('Y-m-d H:i:s');
            $scoreStmt->execute([$users[$u-1], $val, $created]);
            $scoreRows[] = [$db->lastInsertId(), $users[$u-1], $created];
        }

        // Insert posts
        $postStmt = $db->prepare('INSERT INTO posts (user_id, type, body, score_id, created_at) VALUES (?, ?, ?, ?, ?)');
        $textPosts = [
            [1, 'Welcome to StrikeCircle!', '-5 days'],
            [2, 'Ready for league night!', '-4 days'],
            [3, 'Who wants to bowl this weekend?', '-3 days'],
            [1, 'Let’s get a tournament going!', '-2 days'],
            [2, 'Practice makes perfect.', '-1 days'],
            [3, 'Personal best!', 'now'],
            [1, 'Almost a perfect game!', 'now'],
            [2, 'New high score!', 'now'],
            [3, 'Bowling is life!', 'now'],
            [1, 'Strike! Strike! Strike!', 'now'],
        ];
        foreach ($textPosts as [$u, $body, $when]) {
            $created = (new DateTimeImmutable($when))->format('Y-m-d H:i:s');
            $postStmt->execute([$users[$u-1], 'text', $body, null, $created]);
        }
        foreach ($scoreRows as [$scoreId, $userId, $created]) {
            $postStmt->execute([$userId, 'score', 'Score post', $scoreId, $created]);
        }

        // Insert likes
        $likeStmt = $db->prepare('INSERT IGNORE INTO post_reactions (post_id, user_id, type, created_at) VALUES (?, ?, "like", ?)');
        $postIds = $db->query('SELECT id FROM posts ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($postIds as $i => $postId) {
            $likeStmt->execute([$postId, $users[($i+1)%count($users)], 'like', (new DateTimeImmutable("-1 days"))->format('Y-m-d H:i:s')]);
        }

        // Insert comments
        $commentStmt = $db->prepare('INSERT INTO post_comments (post_id, user_id, body, created_at) VALUES (?, ?, ?, ?)');
        $comments = [];
        for ($i = 0; $i < 20; $i++) {
            $postId = $postIds[$i % count($postIds)];
            $userId = $users[($i+2)%count($users)];
            $body = "Comment $i";
            $created = (new DateTimeImmutable("-" . ($i%5) . " days"))->format('Y-m-d H:i:s');
            $comments[] = [$postId, $userId, $body, $created];
        }
        foreach ($comments as $c) {
            $commentStmt->execute($c);
        }
    }
}
