# Word Search Web Application

A modern, browser-based Word Search game built with PHP 8.3+, PostgreSQL, Bootstrap 5, and jQuery. Features user authentication, theme-based puzzles, difficulty levels, and score tracking.

## Features

- 🎯 **Smart Puzzle Generation**: Advanced algorithms for optimal word placement
- 🎨 **Theme System**: 6 built-in themes (Animals, Technology, Food, Geography, Medical, Automotive)
- 📱 **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- 🔐 **User Authentication**: JWT-based login/registration system
- 🏆 **Score Tracking**: Leaderboards and personal statistics
- 🎮 **Multiple Difficulties**: Easy (10×10), Medium (15×15), Hard (20×20)
- 🔄 **Advanced Options**: Diagonal words, reverse words, seeded generation
- 💾 **Database Storage**: PostgreSQL with JSON fallback

## Technology Stack

- **Backend**: PHP 8.3+, PSR-4 autoloading
- **Database**: PostgreSQL with PDO
- **Frontend**: Bootstrap 5.3+, jQuery 3.7+
- **Authentication**: JWT tokens with Firebase/php-jwt
- **Styling**: Custom CSS with green development theme
- **Icons**: Bootstrap Icons

## Requirements

- PHP 8.3 or higher
- PostgreSQL 12 or higher
- Composer
- Web server (Nginx/Apache) with PHP-FPM
- Modern web browser

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd WordSearch/Dev
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file and configure your settings:

```bash
cp env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=wordsearch_dev
DB_USERNAME=wordsearch_dev_user
DB_PASSWORD=your_secure_password
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_EXPIRY=3600
APP_URL=https://your-domain.com
```

### 4. Database Setup

Run the database setup script:

```bash
php setup_database.php
```

This will:
- Create the PostgreSQL database and user
- Create all necessary tables
- Set up indexes and triggers
- Configure proper permissions

### 5. Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/WordSearch/Dev/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Configuration

Ensure `mod_rewrite` is enabled and add to `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 6. File Permissions

Ensure proper permissions for storage directories:

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## Usage

### Starting a New Game

1. Visit the home page
2. Select a theme (Animals, Technology, etc.)
3. Choose difficulty level
4. Configure game options (diagonals, reverse words)
5. Click "Start New Game"

### Game Controls

- **Mouse/Touch**: Click and drag to select words
- **Word List**: Found words are automatically marked
- **Timer**: Tracks completion time
- **Hints**: Available for assistance

### User Features

- **Registration**: Create new account with email verification
- **Login**: Secure JWT-based authentication
- **Profile**: Update personal information
- **Scores**: View personal and global leaderboards

## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/profile` - Get user profile
- `POST /api/auth/profile/update` - Update profile
- `POST /api/auth/password/change` - Change password

### Game
- `POST /api/generate` - Generate new puzzle
- `GET /api/puzzle/{id}` - Get puzzle by ID
- `POST /api/validate` - Validate word selection
- `GET /api/themes` - Get available themes

### Scores
- `GET /api/scores` - Global leaderboards
- `GET /api/scores/my` - Personal scores
- `GET /api/scores/stats` - Global statistics
- `GET /api/scores/my/stats` - Personal statistics

## Development

### Project Structure

```
/wordsearch
├── app/                    # PHP source (PSR-4: App\)
│   ├── Controllers/       # API controllers
│   ├── Services/          # Business logic services
│   ├── Http/             # Routing and middleware
│   └── Utils/            # Utility classes
├── public/                # Web root
│   ├── assets/           # CSS, JS, images
│   ├── views/            # Page templates
│   └── index.php         # Front controller
├── resources/             # Theme word lists
├── storage/               # Logs, puzzles, cache
└── vendor/                # Composer dependencies
```

### Adding New Themes

1. Create a new JSON file in `resources/themes/`
2. Follow the existing format:

```json
{
  "name": "Theme Name",
  "description": "Theme description",
  "difficulty": "medium",
  "words": ["WORD1", "WORD2", "WORD3"]
}
```

### Customizing Styles

Edit `public/assets/css/app.css` to modify the appearance. The application uses Bootstrap 5 with a custom green development theme.

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify PostgreSQL is running
   - Check credentials in `.env`
   - Ensure database and user exist

2. **Puzzle Generation Fails**
   - Check theme JSON files exist
   - Verify storage directory permissions
   - Check error logs

3. **Authentication Issues**
   - Verify JWT secret is set
   - Check token expiration settings
   - Clear browser localStorage

### Debug Mode

Enable debug mode by setting in `.env`:

```env
APP_ENV=development
```

### Logs

Check application logs in:
- `/var/log/nginx/error.log` (Nginx)
- `/var/log/apache2/error.log` (Apache)
- `storage/logs/` (Application logs)

## Performance

- **Word Placement**: Intelligent backtracking with fallback strategies
- **Database**: Optimized queries with proper indexing
- **Caching**: File-based fallback for offline scenarios
- **Grid Generation**: Seeded generation for reproducible puzzles

## Security

- **JWT Authentication**: Secure token-based authentication
- **Password Hashing**: bcrypt password hashing
- **Input Validation**: Server-side validation for all inputs
- **Database Security**: Prepared statements with PDO
- **CORS**: Proper cross-origin resource sharing headers

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Check the troubleshooting section
- Review error logs
- Open an issue on GitHub

## Changelog

### Version 1.0.0
- Initial release
- Core word search functionality
- User authentication system
- Theme-based puzzles
- Responsive design
- Score tracking

---

**Happy Word Searching!** 🎯✨
