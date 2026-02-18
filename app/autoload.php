<?php
// Shared PSR-4-like autoloader for App\ classes
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $path = __DIR__ . '/' . str_replace('App\\', '', $class) . '.php';
        $path = str_replace('\\', '/', $path);
        $fullPath = dirname(__DIR__) . '/app/' . $path;
        if (is_file($fullPath)) require_once $fullPath;
    }
});
