<?php

declare(strict_types=1);

// Test JWT library functionality
echo "=== Testing JWT Library ===\n\n";

// Check if JWT classes exist
if (!class_exists('Firebase\JWT\JWT')) {
    echo "❌ JWT class not found. Checking autoloader...\n";
    
    // Try to load composer autoloader
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "✅ Composer autoloader loaded\n";
        
        if (class_exists('Firebase\JWT\JWT')) {
            echo "✅ JWT class now available\n";
        } else {
            echo "❌ JWT class still not available after autoloader\n";
            exit(1);
        }
    } else {
        echo "❌ Composer autoloader not found\n";
        exit(1);
    }
} else {
    echo "✅ JWT class available\n";
}

// Test JWT encoding
try {
    $payload = [
        'user_id' => 1,
        'username' => 'test',
        'email' => 'test@example.com',
        'iat' => time(),
        'exp' => time() + 3600
    ];
    
    $secret = 'test-secret-key-for-jwt';
    
    echo "Testing JWT encoding...\n";
    $token = Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    echo "✅ JWT token generated successfully\n";
    echo "Token length: " . strlen($token) . " characters\n";
    
    // Test JWT decoding
    echo "Testing JWT decoding...\n";
    $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($secret, 'HS256'));
    echo "✅ JWT token decoded successfully\n";
    echo "Decoded user_id: " . $decoded->user_id . "\n";
    echo "Decoded username: " . $decoded->username . "\n";
    
    echo "\n🎉 JWT library is working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ JWT test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
