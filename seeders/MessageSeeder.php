<?php
// seeders/MessageSeeder.php

declare(strict_types=1);

use App\Database\Seeder;
use App\Database\Faker\SeededRandom;
use App\Database\Factories\MessageFactory;
use PDO;

final class MessageSeeder extends Seeder
{
    public function run(PDO $db): void
    {
        $seed = (int)(getenv('SEED_DATA_SEED') ?: 1337);
        $rand = new SeededRandom($seed + 7000); // Offset for message randomness
        $factory = new MessageFactory($db, $rand);

        // 20 deterministic messages
        for ($i = 0; $i < 20; $i++) {
            $factory->create();
        }
    }
}
            $created = (new DateTimeImmutable("-" . ($i%3) . " days"))->format('Y-m-d H:i:s');
            $msgStmt->execute([$tid, $uid, $body, $created]);
        }
    }
}
