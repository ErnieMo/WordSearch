<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Load configurations
$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'game' => require __DIR__ . '/../config/game.php',
    'paths' => require __DIR__ . '/../config/paths.php',
];

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Error handling
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

return $config;
