<?php

declare(strict_types=1);

// Load the application
$config = require __DIR__ . '/../bootstrap/app.php';

// Create router
$router = new \App\Http\Router($config);

// Define routes
$router->get('/', function($config) {
    return require __DIR__ . '/views/home.php';
});

$router->get('/play', function($config) {
    return require __DIR__ . '/views/play.php';
});

$router->get('/create', function($config) {
    return require __DIR__ . '/views/create.php';
});

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

try {
    $response = $router->dispatch($method, $uri);
    
    if (is_string($response)) {
        echo $response;
    }
} catch (Exception $e) {
    if ($config['app']['debug']) {
        throw $e;
    }
    
    http_response_code(500);
    echo 'Internal Server Error';
}
