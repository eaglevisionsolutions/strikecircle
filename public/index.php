<?php

require_once __DIR__ . '/../app/autoload.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API routing
if (strpos($uri, '/api/v1/') === 0) {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = substr($uri, 8); // after /api/v1/
    if ($path === 'auth/register' && $method === 'POST') { \App\Controllers\Api\AuthController::register(); exit; }
    if ($path === 'auth/login' && $method === 'POST') { \App\Controllers\Api\AuthController::login(); exit; }
    if ($path === 'auth/refresh' && $method === 'POST') { \App\Controllers\Api\AuthController::refresh(); exit; }
    if ($path === 'auth/logout' && $method === 'POST') { \App\Controllers\Api\AuthController::logout(); exit; }
    if ($path === 'auth/me' && $method === 'GET') { \App\Controllers\Api\AuthController::me(); exit; }
    if ($path === 'auth/oauth/google/start' && $method === 'GET') { \App\Controllers\Api\AuthController::oauthStart('google'); exit; }
    if ($path === 'auth/oauth/google/callback' && $method === 'GET') { \App\Controllers\Api\AuthController::oauthCallback('google'); exit; }
    if ($path === 'auth/oauth/facebook/start' && $method === 'GET') { \App\Controllers\Api\AuthController::oauthStart('facebook'); exit; }
    if ($path === 'auth/oauth/facebook/callback' && $method === 'GET') { \App\Controllers\Api\AuthController::oauthCallback('facebook'); exit; }
    // ...other API routes...
    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>['code'=>'NOT_FOUND','message'=>'API endpoint not found.']]);
    exit;
}

// Web routing (simple example)

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/../app/Views/home.php';
    exit;
}
if ($uri === '/login') {
    require __DIR__ . '/../app/Views/login.php';
    exit;
}
if ($uri === '/dashboard') {
    \App\Middleware\Auth::requireAuth();
    ob_start();
    include __DIR__ . '/../app/Views/dashboard.php';
    $content = ob_get_clean();
    include __DIR__ . '/../app/Views/layouts/base.php';
    exit;
}
// ...other web routes...
http_response_code(404);
echo 'Page not found.';
