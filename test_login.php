<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable('.');
$dotenv->load();

echo "=== Login Authentication Test ===\n";

try {
    $db = new App\Services\DatabaseService();
    $authService = new App\Services\AuthService($db);
    
    // Test with the known user
    $username = 'ErnieMo';
    $password = 'password123'; // Try a common password
    
    echo "Testing login with username: $username\n";
    
    try {
        $result = $authService->login($username, $password);
        echo "✅ Login successful!\n";
        echo "User ID: " . $result['user_id'] . "\n";
        echo "Token: " . substr($result['token'], 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "❌ Login failed: " . $e->getMessage() . "\n";
        
        // Let's check what the actual password hash looks like
        $user = $db->fetchOne(
            "SELECT password FROM users WHERE username = :username",
            ['username' => $username]
        );
        
        if ($user) {
            echo "Password hash in database: " . $user['password'] . "\n";
            echo "Hash length: " . strlen($user['password']) . "\n";
            echo "Hash starts with: " . substr($user['password'], 0, 10) . "...\n";
            
            // Test if it's a valid bcrypt hash
            if (password_verify($password, $user['password'])) {
                echo "✅ Password verification successful with bcrypt\n";
            } else {
                echo "❌ Password verification failed with bcrypt\n";
                
                // Try to create a new password hash
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                echo "New hash for '$password': " . $newHash . "\n";
                
                // Test the new hash
                if (password_verify($password, $newHash)) {
                    echo "✅ New hash verification successful\n";
                } else {
                    echo "❌ New hash verification failed\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
