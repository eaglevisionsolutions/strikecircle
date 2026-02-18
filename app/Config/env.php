<?php
namespace App\Config;
declare(strict_types=1);

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

            // Always load .env first (lowest priority)
            $loadFile = function (string $path) {
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
                    // Remove surrounding quotes
                    if ((strlen($v) >= 2) && (($v[0] === '"' && $v[strlen($v) - 1] === '"') || ($v[0] === "'" && $v[strlen($v) - 1] === "'"))) {
                        $v = substr($v, 1, -1);
                    }
                    self::$vars[$k] = $v;
                }
            };

            $loadFile($root . '/.env');

            // Determine env (default local)
            $env = self::$vars['APP_ENV'] ?? getenv('APP_ENV') ?: 'local';
            $envFile = $root . '/.env.' . $env;
            if (is_file($envFile)) {
                $loadFile($envFile);
            }
        }
        return self::$vars[$key] ?? $default;
    }
}
                        $v = substr($v, 1, -1);
                    }

                    $vars[$k] = $v;
                }
            };

            // 1) Load base .env (optional)
            $loadFile($root . '/.env');

            // Determine APP_ENV (env var wins, then .env value, then default)
            $appEnv = getenv('APP_ENV');
            if ($appEnv === false || $appEnv === '') {
                $appEnv = $vars['APP_ENV'] ?? 'local';
            }
            $appEnv = strtolower(trim((string)$appEnv));

            // Map environment to filename
            $suffix = match ($appEnv) {
                'prod', 'production' => 'production',
                'stage', 'staging'   => 'staging',
                default              => 'local',
            };

            // 2) Load env-specific override
            $loadFile($root . '/.env.' . $suffix);

            // Expose loaded vars to getenv() consumers too (non-destructive)
            foreach ($vars as $k => $v) {
                if (getenv($k) === false) {
                    putenv($k . '=' . $v);
                }
                if (!isset($_ENV[$k])) {
                    $_ENV[$k] = $v;
                }
                if (!isset($_SERVER[$k])) {
                    $_SERVER[$k] = $v;
                }
            }
        }

        return $vars[$key] ?? $default;
    }
}