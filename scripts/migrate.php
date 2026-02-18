<?php
require_once __DIR__ . '/../app/autoload.php';
// Migration runner for StrikeCircle
// Usage: php scripts/migrate.php
use \App\Config\DB;

$pdo = DB::connect();

$migrationsDir = __DIR__ . '/../migrations';
if (!is_dir($migrationsDir)) die("Migrations directory not found.\n");

$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$applied = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN) ?: [];

$migrationFiles = glob($migrationsDir . '/*.php');
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    $name = basename($file);
    if (in_array($name, $applied)) continue;
    echo "Applying $name... ";
    $before = get_declared_classes();
    require $file;
    $after = get_declared_classes();
    $new = array_diff($after, $before);
    $ran = false;
    foreach ($new as $cls) {
        if (is_subclass_of($cls, \App\Database\Migration::class)) {
            $migration = new $cls();
            $migration->up($pdo);
            $ran = true;
        }
    }
    if (!$ran) {
        echo "(no Migration class found) ";
    }
    $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)")->execute([$name]);
    echo "done.\n";
}
echo "All migrations applied.\n";



