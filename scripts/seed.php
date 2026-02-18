<?php
// Seed demo user for StrikeCircle
require_once __DIR__ . '/../app/Config/database.php';
$pdo = db_connect();
$email = 'demo@strikecircle.local';
$username = 'demo';
$password = password_hash('DemoPass123!', PASSWORD_DEFAULT);
$exists = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
$exists->execute([$email, $username]);
if ($exists->fetch()) {
    echo "Demo user already exists.\n";

    <?php
    // Seed demo users, posts, likes, comments for StrikeCircle
    require_once __DIR__ . '/../app/Config/database.php';
    $pdo = db_connect();

    $users = [
        ['demo@strikecircle.local', 'demo', 'DemoPass123!'],
        ['alice@strikecircle.local', 'alice', 'AlicePass!'],
        ['bob@strikecircle.local', 'bob', 'BobPass!'],
    ];
    $userIds = [];
    foreach ($users as $u) {
        [$email, $username, $pw] = $u;
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $exists->execute([$email, $username]);
        $row = $exists->fetch();
        if ($row) {
            $userIds[] = $row['id'];
            continue;
        }
        $password = password_hash($pw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (email, username, password, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$email, $username, $password]);
        $userIds[] = $pdo->lastInsertId();
        echo "User created: $email / $pw\n";
    }

    // Insert 8 posts (mix text/score)
    $posts = [
        ['user' => 0, 'type' => 'text', 'body' => 'Welcome to StrikeCircle!'],
        ['user' => 1, 'type' => 'score', 'body' => 'New high score!', 'score' => 245],
        ['user' => 2, 'type' => 'text', 'body' => 'Ready for league night!'],
        ['user' => 0, 'type' => 'score', 'body' => 'Personal best!', 'score' => 210],
        ['user' => 1, 'type' => 'text', 'body' => 'Who wants to bowl this weekend?'],
        ['user' => 2, 'type' => 'score', 'body' => 'Almost a perfect game!', 'score' => 298],
        ['user' => 0, 'type' => 'text', 'body' => 'Let’s get a tournament going!'],
        ['user' => 1, 'type' => 'score', 'body' => 'Practice makes perfect.', 'score' => 180],
    ];

    // Insert scores and posts
    $scoreStmt = $pdo->prepare('INSERT INTO scores (user_id, value, created_at) VALUES (?, ?, NOW())');
    $postStmt = $pdo->prepare('INSERT INTO posts (user_id, type, body, score_id, created_at) VALUES (?, ?, ?, ?, NOW())');
    $postIds = [];
    foreach ($posts as $p) {
        $uid = $userIds[$p['user']];
        if ($p['type'] === 'score') {
            $scoreStmt->execute([$uid, $p['score']]);
            $scoreId = $pdo->lastInsertId();
            $postStmt->execute([$uid, 'score', $p['body'], $scoreId]);
        } else {
            $postStmt->execute([$uid, 'text', $p['body'], null]);
        }
        $postIds[] = $pdo->lastInsertId();
    }

    // Add some likes
    $likeStmt = $pdo->prepare('INSERT IGNORE INTO post_reactions (post_id, user_id, type, created_at) VALUES (?, ?, "like", NOW())');
    $likeStmt->execute([$postIds[0], $userIds[1]]);
    $likeStmt->execute([$postIds[0], $userIds[2]]);
    $likeStmt->execute([$postIds[1], $userIds[0]]);
    $likeStmt->execute([$postIds[2], $userIds[0]]);
    $likeStmt->execute([$postIds[2], $userIds[1]]);

    // Add some comments
    $commentStmt = $pdo->prepare('INSERT INTO post_comments (post_id, user_id, body, created_at) VALUES (?, ?, ?, NOW())');
    $commentStmt->execute([$postIds[0], $userIds[1], 'Awesome!']);
    $commentStmt->execute([$postIds[0], $userIds[2], 'Welcome!']);
    $commentStmt->execute([$postIds[1], $userIds[0], 'Great score!']);
    $commentStmt->execute([$postIds[2], $userIds[1], 'Good luck!']);
