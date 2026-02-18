<?php
namespace App\Config;

// Load environment variables using env.php
use App\Config\Env;
return [
    // Application name
    'app_name' => Env::get('APP_NAME', 'StrikeCircle'),

    // Application environment (local, staging, production)
    'env' => Env::get('APP_ENV', 'local'),

    // Debug mode (true/false)
    'debug' => Env::get('APP_DEBUG', false),

    // Database settings
    'db_host' => Env::get('DB_HOST', 'localhost'),
    'db_port' => Env::get('DB_PORT', '3306'),
    'db_name' => Env::get('DB_NAME', 'strikecircle'),
    'db_user' => Env::get('DB_USER', 'root'),
    'db_pass' => Env::get('DB_PASS', ''),
    'db_charset' => Env::get('DB_CHARSET', 'utf8mb4'),

    // JWT secret
    'jwt_secret' => Env::get('JWT_SECRET', ''),

    // Add more config values as needed, always using Env::get() for flexibility
];
