<?php
declare(strict_types=1);

/**
 * Simple environment loader
 * - Loads <project-root>/.env then <project-root>/.env.<APP_ENV>
 * - APP_ENV defaults to "local" (so it will load .env.local)
 * - Usage: env('KEY', 'default')
 */

if (!function_exists('env')) {

    function env(string $key, mixed $default = null): mixed
    {
        static $vars = null;

        if ($vars === null) {
            $vars = [];

            // app/config -> project root (strikecircle)
            $root = realpath(__DIR__ . '/../../');
            if ($root === false) {
                $root = __DIR__ . '/../../';
            }

            // Helper to load a single .env file into $vars
            $loadFile = function (string $path) use (&$vars): void {
                if (!is_file($path) || !is_readable($path)) {
                    return;
                }

                $lines = file($path, FILE_IGNORE_NEW_LINES);
                if ($lines === false) return;

                foreach ($lines as $line) {
                    $line = trim($line);

                    // Skip empty lines and comments
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }

                    // Allow "export KEY=value"
                    if (str_starts_with($line, 'export ')) {
                        $line = trim(substr($line, 7));
                    }

                    // Must contain "="
                    $pos = strpos($line, '=');
                    if ($pos === false) continue;

                    $k = trim(substr($line, 0, $pos));
                    $v = trim(substr($line, $pos + 1));

                    // Remove surrounding quotes
                    if (
                        (strlen($v) >= 2) &&
                        (($v[0] === '"' && $v[strlen($v) - 1] === '"') ||
                         ($v[0] === "'" && $v[strlen($v) - 1] === "'"))
                    ) {
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