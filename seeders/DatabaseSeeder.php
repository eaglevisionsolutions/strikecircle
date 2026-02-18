<?php
// seeders/DatabaseSeeder.php

declare(strict_types=1);

use App\Database\Seeder;

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

        // Run seeders only if their tables exist
        if ($this->tableExists($db, 'users')) (new UserSeeder())->run($db);
        if ($this->tableExists($db, 'friends')) (new FriendSeeder())->run($db);
        if ($this->tableExists($db, 'scores')) (new ScoreSeeder())->run($db);
        if ($this->tableExists($db, 'posts')) (new PostSeeder())->run($db);
        if ($this->tableExists($db, 'post_reactions')) (new ReactionSeeder())->run($db);
        if ($this->tableExists($db, 'post_comments')) (new CommentSeeder())->run($db);
        if ($this->tableExists($db, 'leagues')) (new LeagueSeeder())->run($db);
        if ($this->tableExists($db, 'tournaments')) (new TournamentSeeder())->run($db);
        if ($this->tableExists($db, 'messages')) (new MessageSeeder())->run($db);
    }

    private function tableExists(PDO $db, string $table): bool
    {
        // MySQL/MariaDB do not allow preparing SHOW statements; quote and query instead
        $like = $db->quote($table);
        $stmt = $db->query("SHOW TABLES LIKE $like");
        return $stmt !== false && (bool)$stmt->fetchColumn();
    }
}
