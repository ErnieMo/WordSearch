<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\DatabaseService;

echo "Testing updated database logging with success/failure results...\n";

try {
    $db = new DatabaseService();
    
    // Test connection
    echo "Testing connection...\n";
    $connected = $db->isConnected();
    echo "Connected: " . ($connected ? 'Yes' : 'No') . "\n";
    
    if ($connected) {
        // Test a simple query (should succeed)
        echo "Testing simple query...\n";
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
        echo "User count: " . ($result['count'] ?? 'N/A') . "\n";
        
        // Test insert (should fail - table doesn't exist)
        echo "Testing insert query (should fail)...\n";
        try {
            $db->insert('test_logging_table', ['test_column' => 'test_value']);
        } catch (Exception $e) {
            echo "Insert failed (expected): " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nCheck the log file at /var/www/html/WordSearch/Dev/log/database.log\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
