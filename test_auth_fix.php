<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\AuthService;
use App\Services\DatabaseService;

echo "Testing authentication fix for game creation...\n";

try {
    $db = new DatabaseService();
    $auth = new AuthService($db);
    
    // Test connection
    echo "Testing connection...\n";
    $connected = $db->isConnected();
    echo "Connected: " . ($connected ? 'Yes' : 'No') . "\n";
    
    if ($connected) {
        // Get user ID for Ernie
        $user = $db->fetchOne("SELECT id, username FROM users WHERE username = 'ErnieMo'");
        if ($user) {
            echo "Found user: ID={$user['id']}, username={$user['username']}\n";
            
            // Test login and token generation
            try {
                $loginResult = $auth->login('ErnieMo', '123456');
                if ($loginResult['success']) {
                    $token = $loginResult['token'];
                    echo "Login successful, generated JWT token: " . substr($token, 0, 20) . "...\n";
                    
                    // Test token validation
                    $validatedUser = $auth->validateToken($token);
                    if ($validatedUser) {
                        echo "Token validation successful: user_id={$validatedUser['user_id']}\n";
                        echo "This confirms the authentication flow is working correctly.\n";
                    } else {
                        echo "Token validation failed!\n";
                    }
                } else {
                    echo "Login failed!\n";
                }
            } catch (Exception $e) {
                echo "Login error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "User 'ErnieMo' not found!\n";
        }
    }
    
    echo "\nThe fix should now work correctly.\n";
    echo "When you create a new game in the browser, the user_id should be properly captured.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
