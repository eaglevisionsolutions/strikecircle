<?php
// seeders/DatabaseSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use PDO;

final class DatabaseSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        // Truncate all tables in safe order (disable FK checks)
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'messages', 'message_threads', 'tournament_matches', 'tournament_participants', 'tournaments',
            'league_scores', 'league_members', 'leagues',
            'post_comments', 'post_reactions', 'posts', 'scores',
            'users',
        ] as $table) {
            if ($this->tableExists($db, $table)) {
                $db->exec("TRUNCATE TABLE `$table`");
            }
        }
        $db->exec('SET FOREIGN_KEY_CHECKS=1');

        // Run all seeders in logical order
        (new UserSeeder())->run($db);
        (new FriendSeeder())->run($db);
        (new ScoreSeeder())->run($db);
        (new PostSeeder())->run($db);
        (new ReactionSeeder())->run($db);
        (new CommentSeeder())->run($db);
        (new LeagueSeeder())->run($db);
        (new TournamentSeeder())->run($db);
        (new MessageSeeder())->run($db);
    }

    private function tableExists(PDO $db, string $table): bool
    {
        $stmt = $db->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }
}
