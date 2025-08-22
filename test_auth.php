<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable('.');
$dotenv->load();

echo "=== Database Connection Test ===\n";

try {
    // Test database connection
    $db = new App\Services\DatabaseService();
    
    if ($db->isConnected()) {
        echo "✅ Database connection successful\n";
        
        // Check if users table exists
        $tables = $db->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        echo "Tables found: " . count($tables) . "\n";
        
        foreach ($tables as $table) {
            echo "- " . $table['table_name'] . "\n";
        }
        
        // Check if users table exists and has data
        if ($db->fetchOne("SELECT 1 FROM information_schema.tables WHERE table_name = 'users'")) {
            echo "✅ Users table exists\n";
            
            $userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users");
            echo "Users in database: " . $userCount['count'] . "\n";
            
            if ($userCount['count'] > 0) {
                $users = $db->fetchAll("SELECT id, username, email, is_active FROM users LIMIT 5");
                echo "Sample users:\n";
                foreach ($users as $user) {
                    echo "- ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
                }
            } else {
                echo "❌ No users found in database\n";
            }
        } else {
            echo "❌ Users table does not exist\n";
        }
        
    } else {
        echo "❌ Database connection failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Environment Variables ===\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? 'SET' : 'NOT SET') . "\n";
echo "JWT_SECRET: " . (isset($_ENV['JWT_SECRET']) ? 'SET' : 'NOT SET') . "\n";
