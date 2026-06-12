<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/bootstrap/app.php';

$routes = require base_path('routes/web.php');
$page = $_GET['page'] ?? 'beranda';

if ($page === 'logout') {
    auth_logout();
    session_start();
    flash('info', 'Anda sudah logout.');
    header('Location: ' . url_for('login', ['status' => 'logout']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post($page);
}

if (! isset($routes[$page])) {
    http_response_code(404);
    view('errors.404', [
        'title' => 'Halaman Tidak Ditemukan',
        'page' => $page,
        'role' => 'guest',
        'data' => psb_data(),
        'flashMessages' => pull_flash(),
    ], 'layouts/guest');
    exit;
}

$route = $routes[$page];
require_role($route['role'] ?? 'guest');

view($routes[$page]['view'], [
    'title' => $route['title'],
    'page' => $page,
    'route' => $route,
    'role' => $route['role'] ?? 'guest',
    'user' => current_user(),
    'data' => psb_data(),
    'flashMessages' => pull_flash(),
], $route['layout'] ?? 'layouts/admin');
