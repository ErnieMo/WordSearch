<?php

declare(strict_types=1);

/**
 * Database Setup Script for WordSearch
 * 
 * This script creates the PostgreSQL database and tables for the WordSearch application.
 * 
 * @author WordSearch Team
 * @version 1.0.0
 * @since 2024-01-01
 */

echo "=== PostgreSQL Database Setup for WordSearch ===\n\n";

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

// Check if PostgreSQL extension is available
if (!extension_loaded('pgsql')) {
    echo "❌ PostgreSQL PHP extension is not installed.\n";
    echo "Please install it first:\n";
    echo "Ubuntu/Debian: sudo apt install php8.3-pgsql\n";
    echo "CentOS/RHEL: sudo yum install php-pgsql\n";
    echo "macOS: brew install php-pgsql\n";
    exit(1);
}

echo "✅ PostgreSQL PHP extension is installed\n";

// Try to connect to PostgreSQL
try {
    // First, connect to default postgres database to create our database
    $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Connected to PostgreSQL server\n";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '{$database}'");
    $db_exists = $stmt->fetch();
    
    if (!$db_exists) {
        echo "Creating database '{$database}'...\n";
        $pdo->exec("CREATE DATABASE {$database}");
        echo "✅ Database '{$database}' created successfully\n";
    } else {
        echo "✅ Database '{$database}' already exists\n";
    }
    
    // Connect to our database
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Connected to database '{$database}'\n\n";
    
    // Create tables
    echo "Creating tables...\n";
    
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                email_verified BOOLEAN DEFAULT FALSE,
                reset_token VARCHAR(255),
                reset_expires TIMESTAMP
            )
        ",
        'puzzles' => "
            CREATE TABLE IF NOT EXISTS puzzles (
                id SERIAL PRIMARY KEY,
                puzzle_id VARCHAR(20) UNIQUE NOT NULL,
                user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
                theme VARCHAR(50) NOT NULL,
                difficulty VARCHAR(20) NOT NULL,
                grid_size INTEGER NOT NULL,
                words JSONB NOT NULL,
                grid JSONB NOT NULL,
                placed_words JSONB NOT NULL,
                seed INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ",
        'games' => "
            CREATE TABLE IF NOT EXISTS games (
                id SERIAL PRIMARY KEY,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                puzzle_id VARCHAR(20) NOT NULL,
                theme VARCHAR(50) NOT NULL,
                difficulty VARCHAR(20) NOT NULL,
                start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                end_time TIMESTAMP,
                elapsed_time INTEGER DEFAULT 0,
                hints_used INTEGER DEFAULT 0,
                words_found INTEGER DEFAULT 0,
                total_words INTEGER NOT NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ",
        'scores' => "
            CREATE TABLE IF NOT EXISTS scores (
                id SERIAL PRIMARY KEY,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                username VARCHAR(50) NOT NULL,
                game_id INTEGER REFERENCES games(id) ON DELETE CASCADE,
                puzzle_id VARCHAR(20) NOT NULL,
                theme VARCHAR(50) NOT NULL,
                difficulty VARCHAR(20) NOT NULL,
                elapsed_time INTEGER,
                hints_used INTEGER DEFAULT 0,
                words_found INTEGER NOT NULL,
                total_words INTEGER NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        "
    ];
    
    foreach ($tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Table '{$table_name}' created successfully\n";
        } catch (PDOException $e) {
            echo "❌ Failed to create table '{$table_name}': " . $e->getMessage() . "\n";
        }
    }
    
    // Create indexes for better performance
    echo "\nCreating indexes...\n";
    
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        "CREATE INDEX IF NOT EXISTS idx_puzzles_puzzle_id ON puzzles(puzzle_id)",
        "CREATE INDEX IF NOT EXISTS idx_puzzles_theme ON puzzles(theme)",
        "CREATE INDEX IF NOT EXISTS idx_puzzles_difficulty ON puzzles(difficulty)",
        "CREATE INDEX IF NOT EXISTS idx_games_user_id ON games(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_games_puzzle_id ON games(puzzle_id)",
        "CREATE INDEX IF NOT EXISTS idx_games_status ON games(status)",
        "CREATE INDEX IF NOT EXISTS idx_games_theme ON games(theme)",
        "CREATE INDEX IF NOT EXISTS idx_games_difficulty ON games(difficulty)",
        "CREATE INDEX IF NOT EXISTS idx_scores_user_id ON scores(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_scores_theme ON scores(theme)",
        "CREATE INDEX IF NOT EXISTS idx_scores_difficulty ON scores(difficulty)",
        "CREATE INDEX IF NOT EXISTS idx_scores_elapsed_time ON scores(elapsed_time)"
    ];
    
    foreach ($indexes as $index_sql) {
        try {
            $pdo->exec($index_sql);
            echo "✅ Index created successfully\n";
        } catch (PDOException $e) {
            echo "❌ Failed to create index: " . $e->getMessage() . "\n";
        }
    }
    
    // Insert demo user if no users exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();
    
    if ($user_count == 0) {
        echo "\nCreating demo user...\n";
        $demo_password = password_hash('password123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, is_active, email_verified) 
                VALUES ('demo', 'demo@example.com', :password, true, true)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $demo_password]);
        echo "✅ Demo user created (demo@example.com / password123)\n";
    }
    
    echo "\n=== Database Setup Complete ===\n";
    echo "✅ All tables created successfully\n";
    echo "✅ Indexes created for better performance\n";
    echo "✅ Demo user available (if no users existed)\n";
    echo "\nYou can now start the application!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    echo "Please check:\n";
    echo "1. PostgreSQL server is running\n";
    echo "2. Database credentials in .env file are correct\n";
    echo "3. User has proper permissions\n";
    echo "\nTo create database and user manually:\n";
    echo "sudo -u postgres psql\n";
    echo "CREATE DATABASE {$database};\n";
    echo "CREATE USER {$username} WITH PASSWORD '{$password}';\n";
    echo "GRANT ALL PRIVILEGES ON DATABASE {$database} TO {$username};\n";
    echo "\\q\n";
}
