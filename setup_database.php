<?php

declare(strict_types=1);

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Word Search Database Setup\n";
echo "=========================\n\n";

// Database connection parameters
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '5432';
$database = $_ENV['DB_DATABASE'] ?? 'wordsearch_dev';
$username = $_ENV['DB_USERNAME'] ?? 'wordsearch_dev_user';
$password = $_ENV['DB_PASSWORD'] ?? 'your_password';

try {
    // Connect to PostgreSQL server (without specifying database)
    $pdo = new PDO("pgsql:host={$host};port={$port}", 'postgres', 'postgres');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to PostgreSQL server successfully.\n";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '{$database}'");
    $dbExists = $stmt->fetch();
    
    if (!$dbExists) {
        echo "Creating database '{$database}'...\n";
        $pdo->exec("CREATE DATABASE {$database}");
        echo "Database created successfully.\n";
    } else {
        echo "Database '{$database}' already exists.\n";
    }
    
    // Check if user exists
    $stmt = $pdo->query("SELECT 1 FROM pg_user WHERE usename = '{$username}'");
    $userExists = $stmt->fetch();
    
    if (!$userExists) {
        echo "Creating user '{$username}'...\n";
        $pdo->exec("CREATE USER {$username} WITH PASSWORD '{$password}'");
        echo "User created successfully.\n";
    } else {
        echo "User '{$username}' already exists.\n";
    }
    
    // Grant privileges
    echo "Granting privileges...\n";
    $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE {$database} TO {$username}");
    $pdo->exec("ALTER USER {$username} CREATEDB");
    echo "Privileges granted successfully.\n";
    
    // Close connection to postgres database
    $pdo = null;
    
    // Connect to the new database
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database '{$database}' successfully.\n";
    
    // Create tables
    echo "\nCreating tables...\n";
    
    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            email_verified BOOLEAN DEFAULT FALSE,
            reset_token VARCHAR(255),
            reset_expires TIMESTAMP
        )
    ");
    echo "✓ Users table created/verified.\n";
    
    // Puzzles table
    $pdo->exec("
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
    ");
    echo "✓ Puzzles table created/verified.\n";
    
    // Games table
    $pdo->exec("
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
            words_found INTEGER NOT NULL,
            total_words INTEGER NOT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Games table created/verified.\n";
    
    // Scores table
    $pdo->exec("
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
    ");
    echo "✓ Scores table created/verified.\n";
    
    // Create indexes for better performance
    echo "\nCreating indexes...\n";
    
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_puzzles_puzzle_id ON puzzles(puzzle_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_puzzles_theme ON puzzles(theme)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_games_user_id ON games(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_games_puzzle_id ON games(puzzle_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scores_user_id ON scores(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scores_theme ON scores(theme)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scores_difficulty ON scores(difficulty)");
    
    echo "✓ Indexes created/verified.\n";
    
    // Create updated_at trigger function
    $pdo->exec("
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.updated_at = CURRENT_TIMESTAMP;
            RETURN NEW;
        END;
        $$ language 'plpgsql';
    ");
    
    // Create triggers for updated_at
    $pdo->exec("
        DROP TRIGGER IF EXISTS update_users_updated_at ON users;
        CREATE TRIGGER update_users_updated_at
            BEFORE UPDATE ON users
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
    ");
    
    $pdo->exec("
        DROP TRIGGER IF EXISTS update_puzzles_updated_at ON puzzles;
        CREATE TRIGGER update_puzzles_updated_at
            BEFORE UPDATE ON puzzles
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
    ");
    
    $pdo->exec("
        DROP TRIGGER IF EXISTS update_games_updated_at ON games;
        CREATE TRIGGER update_games_updated_at
            BEFORE UPDATE ON games
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
    ");
    
    echo "✓ Triggers created/verified.\n";
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now run the application.\n";
    
} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    echo "Make sure PostgreSQL is running and the postgres user has sufficient privileges.\n";
    exit(1);
} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
