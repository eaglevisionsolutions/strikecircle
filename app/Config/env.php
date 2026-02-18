<?php
// Simple environment loader
// Usage: env('KEY', 'default')
if (!function_exists('env')) {
    function env($key, $default = null) {
        static $vars = null;
        if ($vars === null) {
            $vars = [];
            $envPath = __DIR__ . '/../../.env';
            if (file_exists($envPath)) {
                foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (strpos(trim($line), '#') === 0) continue;
                    [$k, $v] = array_map('trim', explode('=', $line, 2) + [1 => '']);
                    $vars[$k] = $v;
                }
            }
        }
        return $vars[$key] ?? $default;
    }
}
