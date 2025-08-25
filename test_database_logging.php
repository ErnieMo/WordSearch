<?php
require_once 'vendor/autoload.php';

_// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\DatabaseService;

echo "Testing database logging...\n";

try {
    $db = new DatabaseService();
    
    // Test connection
    echo "Testing connection...\n";
    $connected = $db->isConnected();
    echo "Connected: " . ($connected ? 'Yes' : 'No') . "\n";
    
    if ($connected) {
        // Test a simple query
        echo "Testing simple query...\n";
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
        echo "User count: " . ($result['count'] ?? 'N/A') . "\n";
        
        // Test insert (will fail if table doesn't exist, but should still log)
        echo "Testing insert query...\n";
        try {
            $db->insert('test_logging', ['test_column' => 'test_value']);
        } catch (Exception $e) {
            echo "Insert failed (expected): " . $e->getMessage() . "\n";
        }
        
        // Test update
        echo "Testing update query...\n";
        try {
            $db->update('test_logging', ['test_column' => 'updated'], ['id' => 1]);
        } catch (Exception $e) {
            echo "Update failed (expected): " . $e->getMessage() . "\n";
        }
        
        // Test delete
        echo "Testing delete query...\n";
        try {
            $db->delete('test_logging', ['id' => 1]);
        } catch (Exception $e) {
            echo "Delete failed (expected): " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nCheck the log file at /var/www/html/WordSearch/Dev/log/database.log\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
