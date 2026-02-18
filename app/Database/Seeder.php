<?php
// app/Database/Seeder.php

declare(strict_types=1);

namespace App\Database;

use PDO;

abstract class Seeder
{
    abstract public function run(PDO $db): void;
}
