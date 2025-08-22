<?php

declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Set default environment if .env file doesn't exist
    $_ENV['APP_ENV'] = 'development';
    $_ENV['APP_DEBUG'] = 'true';
    $_ENV['APP_URL'] = 'https://wordsearch.dev.nofinway.com';
}

// Load configuration
$config = [
    'DB_HOST' => $_ENV['DB_HOST'] ?? 'localhost',
    'DB_PORT' => $_ENV['DB_PORT'] ?? '5432',
    'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? 'wordsearch_dev',
    'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? 'wordsearch_dev_user',
    'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
    'JWT_SECRET' => $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-this-in-production',
    'JWT_EXPIRY' => $_ENV['JWT_EXPIRY'] ?? '3600',
    'SESSION_SECURE' => $_ENV['SESSION_SECURE'] ?? 'true',
    'SESSION_HTTP_ONLY' => $_ENV['SESSION_HTTP_ONLY'] ?? 'true',
    'SESSION_SAME_SITE' => $_ENV['SESSION_SAME_SITE'] ?? 'Strict'
];

// Initialize database service
$dbService = new \App\Services\DatabaseService($config);

// Initialize router
$router = new \App\Http\Router($config, $dbService);

// Define routes
$router->get('/', function() {
    require __DIR__ . '/views/home.php';
});

$router->get('/play', function() {
    require __DIR__ . '/views/play.php';
});

$router->get('/create', function() {
    require __DIR__ . '/views/create.php';
});

$router->get('/scores', function() {
    require __DIR__ . '/views/scores.php';
});

$router->get('/profile', function() {
    require __DIR__ . '/views/profile.php';
});

// API routes
$router->post('/api/auth/register', function() use ($dbService, $config) {
    $authController = new \App\Controllers\AuthController($dbService, $config);
    echo $authController->register();
});

$router->post('/api/auth/login', function() use ($dbService, $config) {
    $authController = new \App\Controllers\AuthController($dbService, $config);
    echo $authController->login();
});

$router->post('/api/auth/logout', function() use ($dbService, $config) {
    $authController = new \App\Controllers\AuthController($dbService, $config);
    echo $authController->logout();
});

$router->get('/api/auth/profile', function() use ($dbService, $config) {
    $authController = new \App\Controllers\AuthController($dbService, $config);
    echo $authController->profile();
});

$router->post('/api/auth/profile/update', function() use ($dbService, $config) {
    $authController = new \App\Controllers\AuthController($dbService, $config);
    echo $authController->updateProfile();
});

$router->post('/api/auth/password/change', function() use ($dbService, $config) {
    $authController = new \App\Controllers\AuthController($dbService, $config);
    echo $authController->changePassword();
});

// Existing API routes
$router->post('/api/generate', function() use ($router) {
    echo $router->handleGeneratePuzzle();
});

$router->get('/api/puzzle/{id}', function($id) use ($router) {
    echo $router->handleGetPuzzle($id);
});

$router->post('/api/validate', function() use ($router) {
    echo $router->handleValidateWord();
});

$router->get('/api/themes', function() use ($router) {
    echo $router->handleGetThemes();
});

// Dispatch the request
$router->dispatch();
