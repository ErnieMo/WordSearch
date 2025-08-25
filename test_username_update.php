<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\AuthService;
use App\Services\DatabaseService;

echo "Testing username update functionality...\n\n";

try {
    $db = new DatabaseService();
    $auth = new AuthService($db);
    
    // Test connection
    echo "Testing connection...\n";
    $connected = $db->isConnected();
    echo "Connected: " . ($connected ? 'Yes' : 'No') . "\n";
    
    if ($connected) {
        // Test login first
        echo "\n--- Testing Login ---\n";
        try {
            $loginResult = $auth->login('Ernie', '123456');
            if ($loginResult['success']) {
                echo "Login successful!\n";
                echo "User ID: " . $loginResult['user_id'] . "\n";
                echo "Username: " . $loginResult['username'] . "\n";
                
                // Test profile update with new username
                echo "\n--- Testing Username Update ---\n";
                $updateData = [
                    'username' => 'ErnieMo_Updated',
                    'first_name' => 'Ernie',
                    'last_name' => 'Moreau'
                ];
                
                try {
                    $updateResult = $auth->updateProfile($loginResult['user_id'], $updateData);
                    if ($updateResult['success']) {
                        echo "Username update successful!\n";
                        
                        // Verify the update
                        $updatedProfile = $auth->getUserProfile($loginResult['user_id']);
                        if ($updatedProfile) {
                            echo "Updated profile:\n";
                            echo "  Username: " . $updatedProfile['username'] . "\n";
                            echo "  First Name: " . $updatedProfile['first_name'] . "\n";
                            echo "  Last Name: " . $updatedProfile['last_name'] . "\n";
                        }
                        
                        // Test updating back to original username
                        echo "\n--- Testing Username Revert ---\n";
                        $revertData = [
                            'username' => 'ErnieMo',
                            'first_name' => 'Ernie',
                            'last_name' => 'Moreau'
                        ];
                        
                        $revertResult = $auth->updateProfile($loginResult['user_id'], $revertData);
                        if ($revertResult['success']) {
                            echo "Username revert successful!\n";
                        } else {
                            echo "Username revert failed: " . $revertResult['error'] . "\n";
                        }
                        
                    } else {
                        echo "Username update failed: " . $updateResult['error'] . "\n";
                    }
                } catch (Exception $e) {
                    echo "Username update error: " . $e->getMessage() . "\n";
                }
                
            } else {
                echo "Login failed!\n";
            }
        } catch (Exception $e) {
            echo "Login error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nUsername update functionality test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
