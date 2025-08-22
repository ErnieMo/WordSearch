# Word Search Web App

A modern, interactive word search puzzle game built with PHP 8.2+, jQuery, and Bootstrap 5. Generate puzzles, play games, and share with friends!

## Features

- **Multiple Difficulty Levels**: Easy (10×10), Medium (12×12), Hard (15×15)
- **Theme Packs**: Animals, Geography, Technology, Food
- **Custom Puzzles**: Create your own word lists
- **Interactive Gameplay**: Click and drag to select words
- **Responsive Design**: Works on desktop and mobile
- **Shareable Links**: Generate unique URLs for each puzzle
- **Timer & Progress**: Track your solving time and progress
- **Hints System**: Get help when stuck
- **Print Support**: Print puzzles for offline solving

## Tech Stack

- **Backend**: PHP 8.2+ with PSR-4 autoloading
- **Frontend**: jQuery 3.7+, Bootstrap 5.3+
- **Storage**: JSON file-based (easily upgradable to database)
- **Server**: Nginx + PHP-FPM
- **Dependencies**: Composer for PHP package management

## Requirements

- PHP 8.2 or higher
- Composer
- Nginx (or Apache with mod_rewrite)
- Modern web browser with JavaScript enabled

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd wordsearch
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Set Up Web Server

#### Nginx Configuration

Create a server block in your Nginx configuration:

```nginx
server {
    server_name wordsearch.local;
    root /path/to/wordsearch/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    access_log /var/log/nginx/wordsearch.access.log;
    error_log /var/log/nginx/wordsearch.error.log warn;
}
```

#### Apache Configuration

Enable mod_rewrite and create a `.htaccess` file in the `public/` directory:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 4. Set Permissions

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### 5. Environment Configuration

```bash
cp env.example .env
# Edit .env with your settings
```

## Usage

### Playing Games

1. **Home Page**: Choose difficulty level and theme
2. **Quick Start**: Jump into a game with default settings
3. **Game Interface**: Click and drag to select words
4. **Progress Tracking**: Monitor found words and time
5. **Hints**: Use hint button when stuck

### Creating Custom Puzzles

1. **Create Page**: Navigate to `/create`
2. **Word List**: Enter your custom words
3. **Settings**: Choose grid size and options
4. **Generate**: Create and preview your puzzle
5. **Share**: Get a shareable link

### API Endpoints

- `POST /api/generate` - Generate new puzzle
- `GET /api/puzzle/{id}` - Retrieve puzzle by ID
- `POST /api/validate` - Validate word selection

## Project Structure

```
wordsearch/
├── app/                    # PHP source code
│   ├── Controllers/       # Application controllers
│   ├── Services/          # Business logic services
│   ├── Models/            # Data models
│   ├── Http/              # HTTP handling (Router)
│   └── Utils/             # Utility classes
├── bootstrap/              # Application bootstrap
├── config/                 # Configuration files
├── public/                 # Web root
│   ├── assets/            # CSS, JS, images
│   └── views/             # Page templates
├── resources/              # Raw assets
├── storage/                # Writable data
│   ├── puzzles/           # Generated puzzles
│   ├── logs/              # Application logs
│   └── cache/             # Cache files
└── tests/                  # PHPUnit tests
```

## Development

### Running Locally

1. **PHP Built-in Server** (for development):
   ```bash
   cd public
   php -S wordsearch.dev.nofinway.com:8000
   ```

2. **Composer Development Server**:
   ```bash
   composer serve
   ```

### Code Style

- Follow PSR-12 coding standards
- Use strict types in all PHP files
- Maintain consistent naming conventions

### Testing

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage
```

## Customization

### Adding New Themes

1. Add word lists to `public/assets/js/app.js`
2. Update theme selection in views
3. Consider adding theme-specific styling

### Modifying Game Logic

- **Puzzle Generation**: Edit `app/Services/PuzzleGenerator.php`
- **Storage**: Modify `app/Services/PuzzleStore.php`
- **Frontend**: Update `public/assets/js/app.js`

### Styling Changes

- **CSS**: Modify `public/assets/css/app.css`
- **Bootstrap**: Override Bootstrap classes as needed
- **Responsive**: Test on various screen sizes

## Deployment

### Production Considerations

1. **Environment**: Set `APP_DEBUG=false` in production
2. **Logging**: Configure proper log rotation
3. **Security**: Enable HTTPS and security headers
4. **Performance**: Consider Redis for caching
5. **Database**: Upgrade to PostgreSQL/MySQL for larger scale

### Docker Support

```dockerfile
FROM php:8.2-fpm
# Add Dockerfile contents here
```

## Troubleshooting

### Common Issues

1. **Permission Errors**: Check storage directory permissions
2. **Routing Issues**: Verify web server configuration
3. **Puzzle Generation**: Check PHP memory limits
4. **Mobile Issues**: Test touch events and responsive design

### Debug Mode

Enable debug mode in `.env`:
```
APP_DEBUG=true
```

Check logs in `storage/logs/app.log`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues and questions:
- Check the troubleshooting section
- Review existing issues
- Create a new issue with detailed information

## Roadmap

- [ ] User accounts and progress tracking
- [ ] Leaderboards and achievements
- [ ] Advanced puzzle types
- [ ] Mobile app version
- [ ] Social features and sharing
- [ ] Analytics and insights

---

**Happy Word Searching!** 🧩✨
