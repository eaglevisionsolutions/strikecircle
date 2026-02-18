<?php
// Front controller router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API routing
if (strpos($uri, '/api/v1/') === 0) {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = substr($uri, 8); // after /api/v1/
    if ($path === 'auth/login' && $method === 'POST') {
        require_once __DIR__ . '/../app/Controllers/Api/AuthController.php';
        AuthController::login();
        exit;
    }
    if ($path === 'auth/logout' && $method === 'POST') {
        require_once __DIR__ . '/../app/Controllers/Api/AuthController.php';
        AuthController::logout();
        exit;
    }
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
    require_once __DIR__ . '/../app/Middleware/Auth.php';
    $user = Auth::requireAuth();
    ob_start();
    include __DIR__ . '/../app/Views/dashboard.php';
    $content = ob_get_clean();
    include __DIR__ . '/../app/Views/layouts/base.php';
    exit;
}
// ...other web routes...
http_response_code(404);
echo 'Page not found.';
