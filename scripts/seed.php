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
    exit;
}
$stmt = $pdo->prepare('INSERT INTO users (email, username, password, created_at) VALUES (?, ?, ?, NOW())');
$stmt->execute([$email, $username, $password]);
echo "Demo user created: $email / DemoPass123!\n";
