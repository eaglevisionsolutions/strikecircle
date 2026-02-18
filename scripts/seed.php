<?php
declare(strict_types=1);
// scripts/seed.php
// Laravel-style seeder runner for StrikeCircle
use App\Database\Seeder;




require_once __DIR__ . '/../app/autoload.php';

// Also load all seeders in /seeders for direct class usage
foreach (glob(__DIR__ . '/../seeders/*.php') as $file) {
    require_once $file;
}

$env = \App\Config\Env::get('APP_ENV', 'local');
if ($env === 'production') {
    fwrite(STDERR, "Seeding is disabled in production.\n");
    exit(1);
}

$db = \App\Config\DB::connect();


$class = null;
$fresh = false;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--class=')) {
        $class = substr($arg, 8);
    }
    if ($arg === '--fresh') {
        $fresh = true;
    }
}

function truncateAllTables(PDO $db): void {
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $db->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $table) {
        if ($table === 'migrations') continue; // preserve migration history
        $db->exec("TRUNCATE TABLE `$table`");
    }
    $db->exec('SET FOREIGN_KEY_CHECKS=1');
}

try {
    if ($fresh) {
        echo "[--fresh] Truncating all tables...\n";
        truncateAllTables($db);
    }
    if ($class) {
        if (!class_exists($class)) {
            fwrite(STDERR, "Seeder class not found: $class\n");
            exit(1);
        }
        $seeder = new $class();
        if (!($seeder instanceof Seeder)) {
            fwrite(STDERR, "$class is not a Seeder.\n");
            exit(1);
        }
        echo "Running seeder: $class\n";
        if (method_exists($db, 'inTransaction') && !$db->inTransaction()) {
            $db->beginTransaction();
        }
        $seeder->run($db);
        if (method_exists($db, 'inTransaction') && $db->inTransaction()) {
            $db->commit();
        }
        echo "[OK] $class\n";
    } else {
        echo "Running DatabaseSeeder...\n";
        if (method_exists($db, 'inTransaction') && !$db->inTransaction()) {
            $db->beginTransaction();
        }
        (new DatabaseSeeder())->run($db);
        if (method_exists($db, 'inTransaction') && $db->inTransaction()) {
            $db->commit();
        }
        echo "[OK] DatabaseSeeder\n";
    }
} catch (Throwable $e) {
    if (isset($db) && method_exists($db, 'inTransaction') && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "[FAIL] " . get_class($e) . " (" . $e->getCode() . "): " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}
