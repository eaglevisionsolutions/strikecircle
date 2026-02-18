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
    // ...other API routes...
    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>['code'=>'NOT_FOUND','message'=>'API endpoint not found.']]);
    exit;
}

// Web routing (simple example)
if ($uri === '/' || $uri === '') {
    $title = 'StrikeCircle';
    ob_start();
    echo '<div class="card"><h1>Welcome to StrikeCircle</h1><p>Connect. Compete. Bowl.</p></div>';
    $content = ob_get_clean();
    include __DIR__ . '/../app/Views/layout_base.php';
    exit;
}
if ($uri === '/login') {
    $title = 'Login';
    ob_start();
    echo '<div class="card"><h2>Login</h2><form id="loginForm"><input type="email" name="email" placeholder="Email" required><br><input type="password" name="password" placeholder="Password" required><br><button type="submit">Login</button></form><div id="loginError" style="color:red;"></div></div>';
    $content = ob_get_clean();
    include __DIR__ . '/../app/Views/layout_base.php';
    exit;
}
// ...other web routes...
http_response_code(404);
echo 'Page not found.';
