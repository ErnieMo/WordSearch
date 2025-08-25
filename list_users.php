<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\DatabaseService;

echo "Listing all users in the database...\n\n";

try {
    $db = new DatabaseService();
    
    // Get all users
    $users = $db->fetchAll("SELECT id, username, email, first_name, last_name FROM users ORDER BY id");
    
    if ($users) {
        echo "Found " . count($users) . " users:\n\n";
        foreach ($users as $user) {
            echo "ID: " . $user['id'] . "\n";
            echo "Username: " . $user['username'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
            echo "---\n";
        }
    } else {
        echo "No users found in the database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
