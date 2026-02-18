<?php
// app/Database/Migration.php

declare(strict_types=1);

namespace App\Database;

use PDO;

abstract class Migration
{
    abstract public function up(PDO $db): void;
    abstract public function down(PDO $db): void;
}
