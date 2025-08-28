<?php

declare(strict_types=1);

/**
 * Test Profile Page Routing Issue
 * 
 * This script specifically tests the profile page routing problem
 * where clicking profile redirects to home page in production
 * 
 * @author Assistant
 * @last_modified 2024-08-27
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for testing
session_start();

echo "<h1>Profile Page Routing Test</h1>\n";
echo "<p>Testing profile page routing and authentication...</p>\n";

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
    
    echo "<h2>Environment Check</h2>\n";
    echo "<p><strong>Database:</strong> {$envVars['DB_DATABASE']}</p>\n";
    echo "<p><strong>Environment:</strong> {$envVars['APP_ENV']}</p>\n";
    
    // Database connection test
    $dsn = "pgsql:host={$envVars['DB_HOST']};port={$envVars['DB_PORT']};dbname={$envVars['DB_DATABASE']}";
    $pdo = new PDO($dsn, $envVars['DB_USERNAME'], $envVars['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Database connection successful</p>\n";
    
    // Test 1: Check if user is logged in
    echo "<h2>Authentication Status</h2>\n";
    
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
    echo "<p><strong>Session exists:</strong> " . ($isLoggedIn ? 'Yes' : 'No') . "</p>\n";
    
    if ($isLoggedIn) {
        echo "<p><strong>User ID:</strong> {$_SESSION['user_id']}</p>\n";
        echo "<p><strong>Username:</strong> {$_SESSION['username']}</p>\n";
        echo "<p><strong>First Name:</strong> " . (isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Not set') . "</p>\n";
        echo "<p><strong>Last Name:</strong> " . (isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'Not set') . "</p>\n";
        echo "<p><strong>Session Age:</strong> " . (time() - (isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 0)) . " seconds</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ No active session found</p>\n";
    }
    
    // Test 2: Check user data in database
    echo "<h2>User Data Check</h2>\n";
    
    if ($isLoggedIn) {
        $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, is_active FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p style='color: green;'>✓ User found in database</p>\n";
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            echo "<tr><th>Field</th><th>Value</th></tr>\n";
            foreach ($user as $key => $value) {
                echo "<tr><td>$key</td><td>" . ($value ?: '<em>NULL</em>') . "</td></tr>\n";
            }
            echo "</table>\n";
            
            // Check for missing data
            if (empty($user['first_name']) || empty($user['last_name'])) {
                echo "<p style='color: red;'>✗ User missing first_name or last_name</p>\n";
            } else {
                echo "<p style='color: green;'>✓ User has complete name data</p>\n";
            }
        } else {
            echo "<p style='color: red;'>✗ User not found in database</p>\n";
        }
    }
    
    // Test 3: Simulate profile page access
    echo "<h2>Profile Page Simulation</h2>\n";
    
    if ($isLoggedIn) {
        // Simulate what the profile page would do
        $profileData = [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'first_name' => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User',
            'last_name' => isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'Name',
            'email' => isset($user['email']) ? $user['email'] : 'No email',
            'is_active' => isset($user['is_active']) ? $user['is_active'] : false
        ];
        
        echo "<p><strong>Profile data that would be displayed:</strong></p>\n";
        echo "<pre>" . print_r($profileData, true) . "</pre>\n";
        
        // Check if profile data is valid
        $hasValidData = !empty($profileData['first_name']) && !empty($profileData['last_name']) && $profileData['is_active'];
        echo "<p><strong>Profile data valid:</strong> " . ($hasValidData ? 'Yes' : 'No') . "</p>\n";
        
        if (!$hasValidData) {
            echo "<p style='color: red;'>✗ This could cause profile page to redirect to home</p>\n";
        }
    }
    
    // Test 4: Check for common routing issues
    echo "<h2>Routing Issue Analysis</h2>\n";
    
    $issues = [];
    
    if (!$isLoggedIn) {
        $issues[] = "No active session - user would be redirected to login/home";
    }
    
    if ($isLoggedIn && (empty($_SESSION['first_name']) || empty($_SESSION['last_name']))) {
        $issues[] = "Session missing name data - could cause profile page errors";
    }
    
    if ($isLoggedIn && isset($user['is_active']) && !$user['is_active']) {
        $issues[] = "User account is inactive - could cause redirect";
    }
    
    if (empty($issues)) {
        echo "<p style='color: green;'>✓ No obvious routing issues detected</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ Potential routing issues:</p>\n";
        echo "<ul>\n";
        foreach ($issues as $issue) {
            echo "<li>$issue</li>\n";
        }
        echo "</ul>\n";
    }
    
    // Test 5: Check production vs development differences
    echo "<h2>Environment Differences</h2>\n";
    
    $currentEnv = isset($envVars['APP_ENV']) ? $envVars['APP_ENV'] : 'unknown';
    echo "<p><strong>Current Environment:</strong> $currentEnv</p>\n";
    
    if ($currentEnv === 'production') {
        echo "<p style='color: blue;'>ℹ Running in production mode</p>\n";
        echo "<p><strong>Common production issues:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Different database credentials</li>\n";
        echo "<li>Session storage location differences</li>\n";
        echo "<li>File permissions issues</li>\n";
        echo "<li>Different PHP configurations</li>\n";
        echo "<li>HTTPS vs HTTP session handling</li>\n";
        echo "</ul>\n";
    }
    
    // Test 6: Session configuration check
    echo "<h2>Session Configuration</h2>\n";
    
    $sessionSavePath = session_save_path();
    $sessionName = session_name();
    $sessionCookieParams = session_get_cookie_params();
    
    echo "<p><strong>Session save path:</strong> $sessionSavePath</p>\n";
    echo "<p><strong>Session name:</strong> $sessionName</p>\n";
    echo "<p><strong>Session cookie lifetime:</strong> {$sessionCookieParams['lifetime']} seconds</p>\n";
    echo "<p><strong>Session cookie path:</strong> {$sessionCookieParams['path']}</p>\n";
    echo "<p><strong>Session cookie domain:</strong> {$sessionCookieParams['domain']}</p>\n";
    echo "<p><strong>Session cookie secure:</strong> " . ($sessionCookieParams['secure'] ? 'Yes' : 'No') . "</p>\n";
    echo "<p><strong>Session cookie httponly:</strong> " . ($sessionCookieParams['httponly'] ? 'Yes' : 'No') . "</p>\n";
    
    // Test 7: Test profile page logic
    echo "<h2>Profile Page Logic Test</h2>\n";
    
    if ($isLoggedIn) {
        // Simulate the profile page authentication check
        $hasValidSession = isset($_SESSION['user_id']) && isset($_SESSION['username']);
        $hasValidData = !empty($_SESSION['first_name']) && !empty($_SESSION['last_name']);
        $userExists = isset($user) && $user['is_active'];
        
        echo "<p><strong>Session validation:</strong> " . ($hasValidSession ? 'Pass' : 'Fail') . "</p>\n";
        echo "<p><strong>Data validation:</strong> " . ($hasValidData ? 'Pass' : 'Fail') . "</p>\n";
        echo "<p><strong>User validation:</strong> " . ($userExists ? 'Pass' : 'Fail') . "</p>\n";
        
        if ($hasValidSession && $hasValidData && $userExists) {
            echo "<p style='color: green;'>✓ Profile page should work correctly</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Profile page may redirect due to validation failure</p>\n";
        }
    }
    
    echo "<h2>Recommendations</h2>\n";
    echo "<ul>\n";
    
    if (!$isLoggedIn) {
        echo "<li>Check if user is properly logged in</li>\n";
        echo "<li>Verify session is being created on login</li>\n";
    }
    
    if ($isLoggedIn && (empty($_SESSION['first_name']) || empty($_SESSION['last_name']))) {
        echo "<li>Update user data to include first_name and last_name</li>\n";
        echo "<li>Check login process to ensure all session data is set</li>\n";
    }
    
    if ($currentEnv === 'production') {
        echo "<li>Check production server error logs</li>\n";
        echo "<li>Verify production database has same data as development</li>\n";
        echo "<li>Check production session configuration</li>\n";
        echo "<li>Verify HTTPS session handling in production</li>\n";
    }
    
    echo "<li>Add logging to profile page to track redirects</li>\n";
    echo "<li>Check browser developer tools for any JavaScript errors</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>\n";
    echo "<p style='color: red;'>✗ " . htmlspecialchars($e->getMessage()) . "</p>\n";
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>\n";
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<hr>\n";
echo "<p><em>Profile routing test completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
