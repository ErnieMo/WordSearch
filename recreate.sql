-- Drop all tables in correct order (due to foreign key constraints)
DROP TABLE IF EXISTS scores CASCADE;
DROP TABLE IF EXISTS wordsearch_games CASCADE;
DROP TABLE IF EXISTS puzzles CASCADE;
-- DROP TABLE IF EXISTS users CASCADE;

-- Recreate users table with proper boolean defaults
-- CREATE TABLE users (
--     id SERIAL PRIMARY KEY,
--     username VARCHAR(50) UNIQUE NOT NULL,
--     email VARCHAR(255) UNIQUE NOT NULL,
--     first_name VARCHAR(50) NOT NULL,
--     last_name VARCHAR(50) NOT NULL,
--     password VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     is_active BOOLEAN DEFAULT TRUE,
--     email_verified BOOLEAN DEFAULT FALSE,
--     reset_token VARCHAR(255),
--     reset_expires TIMESTAMP
-- );

CREATE TABLE wordsearch_games (
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
    words_found_data JSONB DEFAULT '[]',
    total_words INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grid_size INTEGER NOT NULL DEFAULT 15,
    puzzle_data JSONB,
    completion_time INTEGER,
    completed_at TIMESTAMP
);

-- Recreate indexes
-- CREATE INDEX idx_users_email ON users(email);
-- CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_wordsearch_games_user_id ON wordsearch_games(user_id);
CREATE INDEX idx_wordsearch_games_puzzle_id ON wordsearch_games(puzzle_id);
CREATE INDEX idx_wordsearch_games_status ON wordsearch_games(status);
CREATE INDEX idx_wordsearch_games_theme ON wordsearch_games(theme);
CREATE INDEX idx_wordsearch_games_difficulty ON wordsearch_games(difficulty);

-- Note: Demo user will be created using create_demo_user.php script
-- This ensures the password is properly hashed

-- Verify the structure
\d users

select count(*) from wordsearch_games;
select count(*) from users;
