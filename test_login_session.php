<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\AuthService;
use App\Services\DatabaseService;

echo "Testing login and session persistence...\n\n";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "Session ID: " . session_id() . "\n";
echo "Session status: " . session_status() . "\n";
echo "Initial session data:\n";
print_r($_SESSION);

try {
    $db = new DatabaseService();
    $auth = new AuthService($db);
    
    echo "\n--- Testing Login ---\n";
    try {
        $loginResult = $auth->login('ErnieMo', '321321');
        if ($loginResult['success']) {
            echo "Login successful!\n";
            echo "User ID: " . $loginResult['user_id'] . "\n";
            echo "Username: " . $loginResult['username'] . "\n";
            
            echo "\nSession after login:\n";
            print_r($_SESSION);
            
            echo "\nUser logged in (session): " . ($auth->isUserLoggedIn() ? 'Yes' : 'No') . "\n";
            echo "Current user from session:\n";
            print_r($auth->getCurrentUser());
            
            // Test the exact logic used in play.php
            $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
            $currentUsername = $_SESSION['username'] ?? '';
            
            echo "\n--- play.php authentication logic ---\n";
            echo "isset(\$_SESSION['user_id']): " . (isset($_SESSION['user_id']) ? 'Yes' : 'No') . "\n";
            echo "isset(\$_SESSION['username']): " . (isset($_SESSION['username']) ? 'Yes' : 'No') . "\n";
            echo "\$isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . "\n";
            echo "\$currentUsername: " . $currentUsername . "\n";
            
        } else {
            echo "Login failed!\n";
        }
    } catch (Exception $e) {
        echo "Login error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
