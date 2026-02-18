<?php
// Simple environment loader
// Usage: env('KEY', 'default')

if (!function_exists('env')) {

    function env_load_file(string $envPath, array &$vars): void
    {
        if (!file_exists($envPath)) return;

        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;

            // allow: export KEY=VALUE
            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }

            $parts = explode('=', $line, 2);
            $k = trim($parts[0] ?? '');
            $v = trim($parts[1] ?? '');

            if ($k === '') continue;

            // strip optional quotes
            if ((str_starts_with($v, '"') && str_ends_with($v, '"')) ||
                (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
                $v = substr($v, 1, -1);
            }

            $vars[$k] = $v;
        }
    }

    function env($key, $default = null) {
        static $vars = null;

        if ($vars === null) {
            $vars = [];

            // Project root (two levels up from config/)
            $root = realpath(__DIR__ . '/../../');

            // 1) Load base .env first
            env_load_file($root . '/.env', $vars);

            // 2) Decide environment (APP_ENV can be set in .env or container env)
            $appEnv = $vars['APP_ENV'] ?? getenv('APP_ENV') ?: 'local';

            // 3) Load override file based on APP_ENV
            // Your convention: .env.local, .env.staging, .env.production
            $override = match ($appEnv) {
                'local'      => $root . '/.env.local',
                'staging'    => $root . '/.env.staging',
                'production' => $root . '/.env.production',
                default      => null,
            };

            if ($override) {
                env_load_file($override, $vars);
            }
        }

        return $vars[$key] ?? $default;
    }
}