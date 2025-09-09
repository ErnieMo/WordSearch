<?php

declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Also populate system environment variables for getenv() compatibility
foreach ($_ENV as $key => $value) {
    if (!getenv($key)) {
        putenv("$key=$value");
    }
}

// Start session before any output or headers
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters based on environment
    $cookieDomain = ($_ENV['APP_ENV'] ?? 'development') === 'development' 
        ? '.dev.nofinway.com' 
        : '.nofinway.com';
    
    ini_set('session.cookie_domain', $cookieDomain);
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_secure', '0'); // Set to 1 if using HTTPS
    ini_set('session.cookie_httponly', '1');
    
    session_start();
    
    // Debug session creation
    error_log("\n=== INDEX.PHP SESSION STARTED ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
    error_log("\nAPP_ENV: " . ($_ENV['APP_ENV'] ?? 'development'), 3, '/var/www/html/Logs/wordsearch_debug.log');
    error_log("\nCookie domain set to: " . $cookieDomain, 3, '/var/www/html/Logs/wordsearch_debug.log');
    error_log("\nSession ID: " . session_id(), 3, '/var/www/html/Logs/wordsearch_debug.log');
    error_log("\nSession name: " . ini_get('session.name'));
    error_log("\nCookie domain: " . ini_get('session.cookie_domain'));
    error_log("\nCookie path: " . ini_get('session.cookie_path'));
    error_log("\nSession data after start: " . print_r($_SESSION, true));
}

// Set error reporting for development
if ($_ENV['APP_ENV'] ?? 'development' === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Handle CORS for API requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    exit(0);
}

// Set CORS headers for all requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Create router and handle request
try {
    $router = new App\Http\Router();
    $router->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    // Log error
    error_log("\nApplication error: " . $e->getMessage());
    
    // Send error response
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : 'Something went wrong'
    ]);
}
