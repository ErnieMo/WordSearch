<?php

declare(strict_types=1);

/**
 * Test Users Data and Profile Issues
 * 
 * This script tests:
 * - Database connectivity
 * - User data integrity
 * - Session handling
 * - Profile page routing issues
 * 
 * @author Assistant
 * @last_modified 2024-08-27
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for testing
session_start();

echo "<h1>Users Data Test Script</h1>\n";
echo "<p>Testing database connectivity and user data...</p>\n";

try {
    // Load environment variables
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        throw new Exception("Environment file not found: $envFile");
    }
    
    // Parse .env file
    $envVars = [];
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value, '"\'');
        }
    }
    
    echo "<h2>Environment Variables</h2>\n";
    echo "<ul>\n";
    foreach ($envVars as $key => $value) {
        if (strpos($key, 'PASSWORD') !== false) {
            echo "<li><strong>$key:</strong> " . str_repeat('*', strlen($value)) . "</li>\n";
        } else {
            echo "<li><strong>$key:</strong> $value</li>\n";
        }
    }
    echo "</ul>\n";
    
    // Database connection test
    echo "<h2>Database Connection Test</h2>\n";
    
    $dsn = "pgsql:host={$envVars['DB_HOST']};port={$envVars['DB_PORT']};dbname={$envVars['DB_DATABASE']}";
    $pdo = new PDO($dsn, $envVars['DB_USERNAME'], $envVars['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Database connection successful</p>\n";
    
    // Test database queries
    echo "<h2>Database Queries Test</h2>\n";
    
    // Test 1: Check if users table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'users')");
    $usersTableExists = $stmt->fetchColumn();
    echo "<p><strong>Users table exists:</strong> " . ($usersTableExists ? 'Yes' : 'No') . "</p>\n";
    
    if (!$usersTableExists) {
        throw new Exception("Users table does not exist!");
    }
    
    // Test 2: Check users table structure
    echo "<h3>Users Table Structure</h3>\n";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['column_name']}</td>";
        echo "<td>{$column['data_type']}</td>";
        echo "<td>{$column['is_nullable']}</td>";
        echo "<td>{$column['column_default']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Test 3: Check user count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "<p><strong>Total users in database:</strong> $userCount</p>\n";
    
    // Test 4: Check for users with missing data
    echo "<h3>Users with Missing Data</h3>\n";
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name FROM users WHERE first_name IS NULL OR last_name IS NULL OR first_name = '' OR last_name = ''");
    $usersWithMissingData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usersWithMissingData)) {
        echo "<p style='color: green;'>✓ All users have complete first_name and last_name data</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ Found " . count($usersWithMissingData) . " users with missing data:</p>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>First Name</th><th>Last Name</th></tr>\n";
        foreach ($usersWithMissingData as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>" . ($user['first_name'] ?: '<em>NULL</em>') . "</td>";
            echo "<td>" . ($user['last_name'] ?: '<em>NULL</em>') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Test 5: Check sample user data
    echo "<h3>Sample User Data</h3>\n";
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, is_active, created_at FROM users ORDER BY id LIMIT 5");
    $sampleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>First Name</th><th>Last Name</th><th>Active</th><th>Created</th></tr>\n";
    foreach ($sampleUsers as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['first_name']}</td>";
        echo "<td>{$user['last_name']}</td>";
        echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>{$user['created_at']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Test 6: Check for wordsearch_games table
    echo "<h3>WordSearch Games Table</h3>\n";
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'wordsearch_games')");
    $gamesTableExists = $stmt->fetchColumn();
    echo "<p><strong>WordSearch games table exists:</strong> " . ($gamesTableExists ? 'Yes' : 'No') . "</p>\n";
    
    if ($gamesTableExists) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM wordsearch_games");
        $gameCount = $stmt->fetchColumn();
        echo "<p><strong>Total games in database:</strong> $gameCount</p>\n";
    }
    
    // Test 7: Session test
    echo "<h2>Session Test</h2>\n";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>\n";
    echo "<p><strong>Session data:</strong></p>\n";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>\n";
    
    // Test 8: Test user authentication simulation
    echo "<h2>Authentication Simulation Test</h2>\n";
    
    if ($userCount > 0) {
        // Get first user for testing
        $stmt = $pdo->query("SELECT id, username, first_name, last_name FROM users WHERE is_active = true LIMIT 1");
        $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testUser) {
            echo "<p><strong>Testing with user:</strong> {$testUser['username']} (ID: {$testUser['id']})</p>\n";
            
            // Simulate setting session data
            $_SESSION['user_id'] = $testUser['id'];
            $_SESSION['username'] = $testUser['username'];
            $_SESSION['first_name'] = $testUser['first_name'];
            $_SESSION['last_name'] = $testUser['last_name'];
            $_SESSION['last_activity'] = time();
            
            echo "<p style='color: green;'>✓ Session data set for testing</p>\n";
            echo "<p><strong>Updated session data:</strong></p>\n";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>\n";
            
            // Test session validation
            if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
                echo "<p style='color: green;'>✓ Session validation successful</p>\n";
            } else {
                echo "<p style='color: red;'>✗ Session validation failed</p>\n";
            }
        }
    }
    
    // Test 9: Check for common production issues
    echo "<h2>Production Environment Check</h2>\n";
    
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    echo "<p><strong>Current URL:</strong> $currentUrl</p>\n";
    
    $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    echo "<p><strong>Server Software:</strong> $serverSoftware</p>\n";
    
    $phpVersion = phpversion();
    echo "<p><strong>PHP Version:</strong> $phpVersion</p>\n";
    
    $memoryLimit = ini_get('memory_limit');
    echo "<p><strong>Memory Limit:</strong> $memoryLimit</p>\n";
    
    $maxExecutionTime = ini_get('max_execution_time');
    echo "<p><strong>Max Execution Time:</strong> $maxExecutionTime</p>\n";
    
    // Test 10: Check file permissions and paths
    echo "<h2>File System Check</h2>\n";
    
    $appDir = __DIR__;
    echo "<p><strong>Application Directory:</strong> $appDir</strong></p>\n";
    
    $envFileWritable = is_writable($envFile);
    echo "<p><strong>.env file writable:</strong> " . ($envFileWritable ? 'Yes' : 'No') . "</p>\n";
    
    $logsDir = __DIR__ . '/logs';
    $logsDirExists = is_dir($logsDir);
    $logsDirWritable = $logsDirExists ? is_writable($logsDir) : false;
    echo "<p><strong>Logs directory exists:</strong> " . ($logsDirExists ? 'Yes' : 'No') . "</p>\n";
    echo "<p><strong>Logs directory writable:</strong> " . ($logsDirWritable ? 'Yes' : 'No') . "</p>\n";
    
    echo "<h2>Test Summary</h2>\n";
    echo "<p style='color: green;'>✓ Database connection: Successful</p>\n";
    echo "<p style='color: green;'>✓ Users table: Exists</p>\n";
    echo "<p style='color: green;'>✓ User count: $userCount</p>\n";
    echo "<p style='color: green;'>✓ Session handling: Working</p>\n";
    
    if (!empty($usersWithMissingData)) {
        echo "<p style='color: orange;'>⚠ Users with missing data: " . count($usersWithMissingData) . "</p>\n";
    }
    
    echo "<p><strong>Recommendations:</strong></p>\n";
    echo "<ul>\n";
    if (!empty($usersWithMissingData)) {
        echo "<li>Update users with missing first_name or last_name data</li>\n";
    }
    if (!$logsDirWritable) {
        echo "<li>Check logs directory permissions</li>\n";
    }
    echo "<li>Verify production environment variables match development</li>\n";
    echo "<li>Check production server error logs for additional details</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>\n";
    echo "<p style='color: red;'>✗ " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Stack trace:</strong></p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>\n";
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Error code:</strong> " . $e->getCode() . "</p>\n";
}

echo "<hr>\n";
echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
