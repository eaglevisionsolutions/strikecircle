<?php
namespace App\Config;

// Load environment variables using env.php
return [
    // Application name
    'app_name' => env('APP_NAME', 'StrikeCircle'),

    // Application environment (local, staging, production)
    'env' => env('APP_ENV', 'local'),

    // Debug mode (true/false)
    'debug' => env('APP_DEBUG', false),

    // Database settings
    'db_host' => env('DB_HOST', 'localhost'),
    'db_port' => env('DB_PORT', '3306'),
    'db_name' => env('DB_NAME', 'strikecircle'),
    'db_user' => env('DB_USER', 'root'),
    'db_pass' => env('DB_PASS', ''),
    'db_charset' => env('DB_CHARSET', 'utf8mb4'),

    // JWT secret
    'jwt_secret' => env('JWT_SECRET', ''),

    // Add more config values as needed, always using env() for flexibility
];
