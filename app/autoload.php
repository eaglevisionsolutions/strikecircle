<?php
// Shared PSR-4-like autoloader for App\ classes
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $baseDir = dirname(__DIR__) . '/app/';
        $relative = str_replace('App\\', '', $class);
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) require_once $file;
    }
});
