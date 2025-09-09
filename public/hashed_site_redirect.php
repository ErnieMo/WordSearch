<?php
declare(strict_types=1);

/**
 * Hashed Site Redirect for Cross-Subdomain Authentication
 * 
 * @author WordSearch Team
 * @last_modified 2024-01-01
 */

// Log file inclusion for debugging
error_log(__FILE__ . PHP_EOL, 3, __DIR__ . '/../../../../Logs/included_files.log');

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

require_once __DIR__ . '/../app/Services/DatabaseService.php';
require_once __DIR__ . '/../app/Services/AuthService.php';

use App\Services\DatabaseService;
use App\Services\AuthService;

// Start session
session_start();

// Get redirect URL from query parameter first
$redirectUrl = $_GET['url'] ?? '';
if (empty($redirectUrl)) {
    http_response_code(400);
    die('Bad Request: Missing redirect URL parameter.');
}

// Initialize services
$db = new DatabaseService();
$auth = new AuthService($db);

// Check if user is logged in
if (!$auth->isUserLoggedIn()) {
    // Redirect to target site's home page
    header('Location: ' . $redirectUrl . '/');
    exit;
}

// Validate redirect URL (basic security check)
$allowedDomains = [
    'sudoku.dev.nofinway.com',
    'tileslider.dev.nofinway.com', 
    'wordsearch.dev.nofinway.com',
    'sudoku.nofinway.com',
    'tileslider.nofinway.com',
    'wordsearch.nofinway.com'
];

$parsedUrl = parse_url($redirectUrl);
if (!$parsedUrl || !in_array($parsedUrl['host'] ?? '', $allowedDomains)) {
    http_response_code(400);
    die('Bad Request: Invalid redirect URL domain.');
}

try {
    // Get current user
    $user = $auth->getCurrentUser();
    error_log("WordSearch hashed_site_redirect - User: " . print_r($user, true));
    
    // Generate a secure access token
    $accessToken = bin2hex(random_bytes(32));
    error_log("WordSearch hashed_site_redirect - Generated token: " . $accessToken, 3, '/var/www/html/Logs/wordsearch_debug.log');
    
    // Set token expiry to 1 minute from now
    $expiryTime = date('Y-m-d H:i:s', time() + 60);
    
    // Store token in database
    $result = $db->query(
        'UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :user_id',
        [
            'token' => $accessToken,
            'expires' => $expiryTime,
            'user_id' => $user['user_id']
        ]
    );
    error_log("WordSearch hashed_site_redirect - Database update result: " . print_r($result, true));
    
    // Logout current user - clear session variables
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['first_name']);
    unset($_SESSION['last_name']);
    unset($_SESSION['default_theme']);
    unset($_SESSION['default_level']);
    unset($_SESSION['default_diagonals']);
    unset($_SESSION['default_reverse']);
    unset($_SESSION['authenticated']);
    
    // Force redirect URL to always start from root path
    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    if (isset($parsedUrl['port'])) {
        $baseUrl .= ':' . $parsedUrl['port'];
    }
    
    // Build the redirect URL with access token, always starting from /
    $finalUrl = $baseUrl . '/?access_token=' . urlencode($accessToken);
    
    // Log the complete redirect URL
    $logEntry = date('Y-m-d H:i:s') . " - WordSearch redirect to: " . $finalUrl . " (User ID: " . $user['user_id'] . ")" . PHP_EOL;
    file_put_contents('/var/www/html/Logs/transfer_to_domains.log', $logEntry, FILE_APPEND | LOCK_EX);
    
    // Redirect to the target site
    header('Location: ' . $finalUrl);
    exit;
    
} catch (Exception $e) {
    error_log("Hashed redirect error: " . $e->getMessage());
    http_response_code(500);
    die('Internal Server Error: Failed to generate access token.');
}
