
# Cursor "Remember" Document — Word Search Web App

> **Purpose:** This document tells Cursor/ChatGPT exactly what we're building, the tech choices, conventions, and where things live. Treat it as persistent project memory. When generating code, follow these rules by default.

---

## 1) Project Overview

* **App:** Browser-based **Word Search game** with user authentication and database storage.
* **Goals:** Fast, simple, mobile-friendly; users can play generated puzzles, create custom lists, track scores, and manage profiles.
* **Primary stack:** **PHP 8.3+** + **Nginx** + **PostgreSQL** on Linux, **jQuery** for interactivity, **Bootstrap 5** for styling, **Composer** for PHP deps/autoloading.
* **Style:** Clean Bootstrap UI with green development theme; responsive grid/cards/buttons.

---

## 2) Default Tech Assumptions

* **PHP:** 8.3+; strict types in new classes; PSR-12 coding style.
* **Composer:** PSR-4 autoloading; vlucas/phpdotenv for environment variables.
* **Nginx:** Standard PHP-FPM setup; friendly URLs routed to `public/index.php`.
* **Frontend:** jQuery 3.7+, Bootstrap 5.3+, icons via Bootstrap Icons.
* **Database:** **PostgreSQL** with PDO; fallback to file-based JSON storage.
* **Authentication:** JWT-based with Firebase/php-jwt library.

---

## 3) File/Folder Structure (current implementation)

```
/wordsearch
├── app/                    # PHP source (PSR-4: App\)
│   ├── Controllers/
│   │   ├── AuthController.php      # User authentication endpoints
│   │   └── ScoresController.php    # Score tracking and statistics
│   ├── Services/
│   │   ├── AuthService.php         # JWT authentication logic
│   │   ├── DatabaseService.php     # PDO database wrapper
│   │   ├── PuzzleGenerator.php     # Word search grid generation
│   │   ├── PuzzleStore.php         # File-based puzzle storage (fallback)
│   │   └── ThemeService.php        # Theme word lists management
│   ├── Http/
│   │   └── Router.php              # Application routing and API handling
│   └── Utils/
├── public/                 # Web root served by Nginx
│   ├── index.php           # Front controller / router entry
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css
│   │   └── js/
│   │       └── app.js      # Main game logic and UI interactions
│   └── views/              # PHP partials for layout and pages
│       ├── layout.php      # Main layout with navigation and auth
│       ├── home.php        # Theme/difficulty selection
│       ├── play.php        # Game interface
│       ├── create.php      # Custom puzzle creation
│       ├── scores.php      # Leaderboards and statistics
│       └── profile.php     # User profile management
├── resources/              # Theme word lists
│   └── themes/
│       ├── animals.json    # Animal-themed words
│       ├── automotive.json # Car/vehicle words
│       ├── food.json       # Food and cuisine words
│       ├── geography.json  # Countries and places
│       ├── medical.json    # Healthcare terms
│       └── technology.json # Tech and computer words
├── storage/                # Writable dir (logs, json data, cache)
│   ├── logs/
│   ├── puzzles/            # Saved/generated puzzles (.json fallback)
│   └── cache/
├── vendor/
├── composer.json
├── composer.lock
├── .env                    # Environment config (database, JWT secrets)
├── setup_database.php      # Database schema creation
├── create_demo_user.php    # Demo user creation script
├── recreate.sql            # Complete database recreation script
└── README.md
```

---

## 4) Routing & Pages

* **Routing:** Internal router (`app/Http/Router.php`) mapping clean paths to controller callables.
* **Pages:**

  * `/` → Home (Theme selector, Difficulty selector, New Game button)
  * `/play` → Active puzzle UI (grid, word list, timer, hint button)
  * `/create` → Custom word list form; generate & share link
  * `/scores` → Global leaderboards and user statistics
  * `/profile` → User profile management and game history
  * `/api/*` → JSON endpoints (see below)

---

## 5) Core Features & Current Scope

* **Puzzle Generation:** Generate word search grids from theme word lists with intelligent placement algorithms.
* **Smart Word Placement:** Automatic fallback to shorter words if long words can't fit; grid size optimization.
* **Interactive UI:** Click+drag word selection (mouse) and touch support; real-time highlighting.
* **Difficulty System:** Easy (10×10), Medium (15×15), Hard (20×20) with diagonal and reverse options.
* **Theme System:** 6 built-in themes (Animals, Automotive, Food, Geography, Medical, Technology).
* **User Authentication:** Registration, login, profile management with JWT tokens.
* **Score Tracking:** Game completion times, hints used, leaderboards.
* **Database Storage:** PostgreSQL for users, puzzles, games, and scores with JSON fallback.

---

## 6) API Endpoints (PHP, return JSON)

### Authentication Endpoints:
* `POST /api/auth/register` → User registration with first/last name
* `POST /api/auth/login` → User login and JWT token generation
* `POST /api/auth/logout` → User logout
* `GET /api/auth/profile` → Get user profile information
* `POST /api/auth/profile/update` → Update user profile
* `POST /api/auth/password/change` → Change user password

### Game Endpoints:
* `POST /api/generate` → Generate new puzzle: `{ words: string[], options: { size:int, diagonals:bool, reverse:bool, seed?:int } }` → returns `{ success: true, id: string, message: string }`
* `GET /api/puzzle/{id}` → Returns saved puzzle by ID
* `POST /api/validate` → Validates found word selections
* `GET /api/themes` → Returns available themes and word lists

### Score Endpoints:
* `GET /api/scores` → Global leaderboards
* `GET /api/scores/my` → User's personal scores
* `GET /api/scores/stats` → Global statistics
* `GET /api/scores/my/stats` → User's personal statistics

---

## 7) PHP Services (current implementation)

### Core Services:
* `App\Services\PuzzleGenerator` — Advanced word placement with:
  * Smart backtracking and retry logic
  * Automatic word replacement for difficult placements
  * Grid size optimization
  * Seeded generation for reproducibility
  * Direction randomization (horizontal, vertical, diagonal)
  * Reverse word support

* `App\Services\DatabaseService` — PDO wrapper with:
  * Connection management
  * Transaction support
  * Prepared statement handling
  * Boolean and empty string handling for PostgreSQL

* `App\Services\AuthService` — JWT authentication:
  * User registration and login
  * Password hashing and verification
  * JWT token generation and validation
  * Profile management

* `App\Services\ThemeService` — Theme management:
  * Dynamic theme loading from JSON files
  * Word list management
  * Theme detection from word lists

* `App\Services\PuzzleStore` — File-based storage fallback:
  * JSON puzzle storage
  * Automatic directory creation
  * Fallback when database fails

---

## 8) Database Schema (PostgreSQL)

### Users Table:
```sql
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
```

### Puzzles Table:
```sql
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
```

### Games Table:
```sql
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
    words_found INTEGER NOT NULL,
    total_words INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Scores Table:
```sql
CREATE TABLE scores (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    username VARCHAR(50) NOT NULL,
    game_id INTEGER REFERENCES wordsearch_games(id) ON DELETE CASCADE,
    puzzle_id VARCHAR(20) NOT NULL,
    theme VARCHAR(50) NOT NULL,
    difficulty VARCHAR(20) NOT NULL,
    elapsed_time INTEGER,
    hints_used INTEGER DEFAULT 0,
    words_found INTEGER NOT NULL,
    total_words INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 9) Frontend Conventions (jQuery + Bootstrap)

* **jQuery only** for DOM/state; no global pollution — scope code in IIFEs.
* **Bootstrap 5** layout: `.container`, `.row`, `.col` with responsive breakpoints.
* **Development Theme:** Green navigation bar with "Development Environment" banner.
* **Grid Interaction:** Table-based grid with `data-r`, `data-c`, `data-ch` attributes.
* **Selection UX:** mousedown/touchstart → drag → mouseup for word selection.
* **Authentication UI:** Modal-based login/register forms with auto-login after registration.

---

## 10) Configuration (current implementation)

### Environment Variables (.env):
```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=wordsearch_dev
DB_USERNAME=wordsearch_dev_user
DB_PASSWORD=your_password
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_EXPIRY=3600
APP_URL=https://wordsearch.dev.nofinway.com
```

### Game Configuration:
```php
'game' => [
    'directions' => [
        'horizontal' => [0, 1],
        'vertical' => [1, 0],
        'diagonal_down' => [1, 1],
        'diagonal_up' => [-1, 1]
    ],
    'maxWordLen' => 15,
    'alphabet' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
],
'paths' => [
    'storage' => __DIR__ . '/../storage',
    'resources' => __DIR__ . '/../resources'
]
```

---

## 11) Composer Setup (current)

**composer.json:**
```json
{
  "name": "wem/wordsearch",
  "type": "project",
  "require": {
    "php": ">=8.3",
    "vlucas/phpdotenv": "^5.6",
    "firebase/php-jwt": "^6.8"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "scripts": {
    "post-install-cmd": [
      "@php -r \"if(!is_dir('storage/logs')) mkdir('storage/logs', 0775, true);\"",
      "@php -r \"if(!is_dir('storage/puzzles')) mkdir('storage/puzzles', 0775, true);\"",
      "@php -r \"if(!is_dir('storage/cache')) mkdir('storage/cache', 0775, true);\""
    ]
  },
  "config": {
    "optimize-autoloader": true,
    "sort-packages": true
  }
}
```

---

## 12) Nginx Configuration (current)

* **Web root:** `/var/www/html/WordSearch/Dev/public`
* **Server name:** `wordsearch.dev.nofinway.com`
* **PHP-FPM:** Unix socket `/run/php/php8.3-fpm.sock`
* **Routing:** All non-file requests → `public/index.php`

---

## 13) Security & Quality (current)

* **JWT Authentication:** Secure token-based authentication
* **Password Hashing:** bcrypt password hashing
* **Input Validation:** Server-side validation for all inputs
* **Database Security:** Prepared statements with PDO
* **Error Handling:** Comprehensive error handling with logging
* **Fallback Systems:** File storage fallback when database fails

---

## 14) Current Features & Capabilities

### ✅ **Implemented:**
* User registration and authentication
* Theme-based puzzle generation
* Smart word placement with fallbacks
* Database storage with PostgreSQL
* Score tracking and leaderboards
* Responsive Bootstrap UI
* JWT token management
* Auto-login after registration
* Profile management
* Game statistics

### 🔄 **In Progress:**
* Puzzle retrieval optimization
* Database error handling improvements

### 📋 **Planned:**
* Dark mode toggle
* Print-friendly stylesheets
* PDF export functionality
* Advanced puzzle sharing
* Social features

---

## 15) Development Commands

### Database Setup:
```bash
# Create database and tables
php setup_database.php

# Recreate entire database
psql -U postgres -d wordsearch_dev -f recreate.sql

# Create demo user
php create_demo_user.php
```

### Environment:
```bash
# Install dependencies
composer install

# Clear cache
composer clear-cache
composer dump-autoload
```

---

## 16) Troubleshooting

### Common Issues:
* **Puzzle Generation Fails:** Check database connection and game configuration
* **Authentication Errors:** Verify JWT secret and database user permissions
* **Theme Loading Issues:** Ensure resources/themes directory exists with JSON files
* **Database Connection:** Verify PostgreSQL service and credentials

### Debug Tools:
* **Error Logs:** `/var/log/nginx/error.log`
* **Debug Scripts:** `debug_puzzle.php` for database diagnostics
* **Console Logs:** Browser developer tools for frontend issues

---

## 17) Performance & Optimization

* **Word Placement:** Intelligent backtracking with fallback strategies
* **Database Queries:** Optimized with proper indexes
* **Caching:** File-based fallback for offline scenarios
* **Grid Generation:** Seeded generation for reproducible puzzles

---

**Remember:** This is a production-ready WordSearch application with full user authentication, database storage, and advanced puzzle generation. Use PostgreSQL as primary storage with JSON fallback. Follow PSR-12 coding standards and maintain the green development theme.
