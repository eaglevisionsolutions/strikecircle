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
        $title = 'Dashboard';
        ob_start();
        ?>
        <div class="card">
            <h2>Dashboard</h2>
            <p>Logged in as <b><?= htmlspecialchars($user['username']) ?></b></p>
            <form id="logoutForm"><button type="submit">Logout</button></form>
            <div id="logoutMsg" style="color:green;"></div>
        </div>
        <script>
        $(function() {
            $('#logoutForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/api/v1/auth/logout',
                    method: 'POST',
                    success: function() {
                        localStorage.removeItem('access_token');
                        window.location.href = '/login';
                    }
                });
            });
        });
        </script>
        <?php
        $content = ob_get_clean();
        include __DIR__ . '/../app/Views/layouts/base.php';
        exit;
}
// ...other web routes...
http_response_code(404);
echo 'Page not found.';
