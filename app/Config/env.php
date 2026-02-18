<?php
declare(strict_types=1);
namespace App\Config;
/**
 * Robust environment loader for CLI and web
 * Loads .env (always), then .env.<env> (local, staging, production) if APP_ENV is set
 * Priority: .env < .env.<env> (higher wins)
 * Usage: Env::get('KEY', 'default')
 */
class Env {
    protected static ?array $vars = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$vars === null) {
            self::$vars = [];
            $root = realpath(__DIR__ . '/../../');
            if ($root === false) $root = __DIR__ . '/../../';

            $loadFile = function (string $path): void {
                if (!is_file($path) || !is_readable($path)) return;
                $lines = file($path, FILE_IGNORE_NEW_LINES);
                if ($lines === false) return;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) continue;
                    if (str_starts_with($line, 'export ')) $line = trim(substr($line, 7));
                    $pos = strpos($line, '=');
                    if ($pos === false) continue;
                    $k = trim(substr($line, 0, $pos));
                    $v = trim(substr($line, $pos + 1));
                    if ((strlen($v) >= 2) && (($v[0] === '"' && $v[strlen($v) - 1] === '"') || ($v[0] === "'" && $v[strlen($v) - 1] === "'"))) {
                        $v = substr($v, 1, -1);
                    }
                    self::$vars[$k] = $v;
                }
            };

            $loadFile($root . '/.env');
            $env = self::$vars['APP_ENV'] ?? getenv('APP_ENV') ?: 'local';
            $envFile = $root . '/.env.' . $env;
            if (is_file($envFile)) $loadFile($envFile);
        }
        return self::$vars[$key] ?? $default;
    }
}