<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\DatabaseService;

echo "Testing password verification...\n\n";

try {
    $db = new DatabaseService();
    
    // Get user password hash
    $user = $db->fetchOne("SELECT username, password FROM users WHERE username = 'ErnieMo'");
    if ($user) {
        echo "User found: " . $user['username'] . "\n";
        echo "Password hash: " . $user['password'] . "\n\n";
        
        // Test various passwords
        $testPasswords = [
            '123456',
            'password',
            'admin',
            'test',
            'demo',
            'user',
            'ernie',
            'Ernie',
            'ERNIE',
            '123',
            '1234',
            '12345',
            '1234567',
            '12345678',
            'qwerty',
            'abc123',
            'letmein'
        ];
        
        echo "Testing passwords:\n";
        foreach ($testPasswords as $testPwd) {
            $isValid = password_verify($testPwd, $user['password']);
            echo "  '{$testPwd}': " . ($isValid ? '✓ CORRECT' : '✗ wrong') . "\n";
            if ($isValid) {
                echo "  Found correct password: '{$testPwd}'\n";
                break;
            }
        }
        
        // Test creating a new hash for '123456'
        echo "\nCreating new hash for '123456':\n";
        $newHash = password_hash('123456', PASSWORD_BCRYPT);
        echo "New hash: " . $newHash . "\n";
        echo "Verification: " . (password_verify('123456', $newHash) ? '✓ Works' : '✗ Failed') . "\n";
        
    } else {
        echo "User 'ErnieMo' not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
