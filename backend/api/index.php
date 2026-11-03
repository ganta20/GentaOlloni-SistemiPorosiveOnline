<?php
// Router: lexon URL-në dhe e dërgon te handler-i i duhur.
// Shembull: POST /api/auth/login  ->  handle_auth('POST', 'login')

require_once __DIR__ . '/../helpers/response.php';

// Gabimet PHP nuk duhet të dalin si HTML; i kthejmë si JSON.
set_exception_handler(function ($e) {
    json_error(500, 'SERVER_ERROR', 'Ndodhi një gabim i brendshëm.');
});

$method = $_SERVER['REQUEST_METHOD'];

// Heq pjesën e rrugës deri te /api/ dhe merr segmentet e mbetura.
$path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$position = strpos($path, '/api/');
$route    = $position === false ? '' : substr($path, $position + 5);
$parts    = array_values(array_filter(explode('/', trim($route, '/')), 'strlen'));

$resource = $parts[0] ?? '';
$second   = $parts[1] ?? null;

switch ($resource) {
    case 'auth':
        require_once __DIR__ . '/auth.php';
        handle_auth($method, $second ?? '');
        break;

    case 'products':
        require_once __DIR__ . '/products.php';
        handle_products($method, $second);
        break;

    case 'orders':
        require_once __DIR__ . '/orders.php';
        handle_orders($method, $second);
        break;

    default:
        json_error(404, 'NOT_FOUND', 'Endpoint-i nuk ekziston.');
}