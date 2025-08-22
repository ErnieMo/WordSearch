<?php

declare(strict_types=1);

/**
 * Create Demo User Script
 * 
 * This script creates a demo user with a properly hashed password.
 * Run this after recreating the database tables.
 */

echo "=== Creating Demo User ===\n\n";

// Load environment variables
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    $env_lines = explode("\n", $env_content);
    $env_vars = [];

    foreach ($env_lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $env_vars[$parts[0]] = $parts[1];
        }
    }
} else {
    echo "❌ .env file not found. Please create one from .env.example\n";
    exit(1);
}

// Database configuration
$host = $env_vars['DB_HOST'] ?? 'localhost';
$port = $env_vars['DB_PORT'] ?? '5432';
$database = $env_vars['DB_DATABASE'] ?? 'wordsearch_dev';
$username = $env_vars['DB_USERNAME'] ?? 'wordsearch_dev_user';
$password = $env_vars['DB_PASSWORD'] ?? '';

echo "Database Configuration:\n";
echo "Host: {$host}\n";
echo "Port: {$port}\n";
echo "Database: {$database}\n";
echo "Username: {$username}\n";
echo "Password: " . (empty($password) ? 'Not set' : 'Set') . "\n\n";

try {
    // Connect to database
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Connected to database '{$database}'\n\n";
    
    // Check if demo user already exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'demo'");
    $user_count = $stmt->fetchColumn();
    
    if ($user_count > 0) {
        echo "Demo user already exists. Updating password...\n";
        
        // Update existing demo user
        $demo_password = password_hash('password123', PASSWORD_DEFAULT);
        $sql = "UPDATE users SET 
                first_name = 'Demo', 
                last_name = 'User', 
                email = 'demo@example.com', 
                password = :password, 
                is_active = true, 
                email_verified = true 
                WHERE username = 'demo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $demo_password]);
        
        echo "✅ Demo user updated successfully\n";
    } else {
        echo "Creating new demo user...\n";
        
        // Create new demo user
        $demo_password = password_hash('password123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, first_name, last_name, password, is_active, email_verified) 
                VALUES ('demo', 'demo@example.com', 'Demo', 'User', :password, true, true)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $demo_password]);
        
        echo "✅ Demo user created successfully\n";
    }
    
    echo "\nDemo User Credentials:\n";
    echo "Username: demo\n";
    echo "Email: demo@example.com\n";
    echo "Password: password123\n";
    echo "\nYou can now login with these credentials!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
