-- Drop all tables in correct order (due to foreign key constraints)
DROP TABLE IF EXISTS scores CASCADE;
DROP TABLE IF EXISTS games CASCADE;
DROP TABLE IF EXISTS puzzles CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Recreate users table with proper boolean defaults
CREATE TABLE users (
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
);

-- Recreate other tables
CREATE TABLE puzzles (
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
);

CREATE TABLE games (
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
);

CREATE TABLE scores (
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
);

-- Recreate indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_puzzles_puzzle_id ON puzzles(puzzle_id);
CREATE INDEX idx_puzzles_theme ON puzzles(theme);
CREATE INDEX idx_puzzles_difficulty ON puzzles(difficulty);
CREATE INDEX idx_games_user_id ON games(user_id);
CREATE INDEX idx_games_puzzle_id ON games(puzzle_id);
CREATE INDEX idx_games_status ON games(status);
CREATE INDEX idx_games_theme ON games(theme);
CREATE INDEX idx_games_difficulty ON games(difficulty);
CREATE INDEX idx_scores_user_id ON scores(user_id);
CREATE INDEX idx_scores_theme ON scores(theme);
CREATE INDEX idx_scores_difficulty ON scores(difficulty);
CREATE INDEX idx_scores_elapsed_time ON scores(elapsed_time);

-- Note: Demo user will be created using create_demo_user.php script
-- This ensures the password is properly hashed

-- Verify the structure
\d users

select count(*) from games;
select count(*) from scores;
select count(*) from users;
select count(*) from puzzles;
