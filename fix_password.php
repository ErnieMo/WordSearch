<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable('.');
$dotenv->load();

echo "=== Fixing User Password ===\n";

try {
    $db = new App\Services\DatabaseService();
    
    // Update the user's password to 'password123'
    $newPassword = 'password123';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $result = $db->update('users', 
        ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
        ['username' => 'ErnieMo']
    );
    
    if ($result) {
        echo "✅ Password updated successfully\n";
        echo "New password: $newPassword\n";
        echo "New hash: " . $hashedPassword . "\n";
        
        // Test the login now
        $authService = new App\Services\AuthService($db);
        
        try {
            $loginResult = $authService->login('ErnieMo', $newPassword);
            echo "✅ Login test successful!\n";
            echo "User ID: " . $loginResult['user_id'] . "\n";
            echo "Token: " . substr($loginResult['token'], 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "❌ Login test still failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Failed to update password\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
