<?php
require_once 'vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable('.');
    $dotenv->load();
} catch (Exception $e) {
    echo "No .env file found, using defaults\n";
}

// Load configuration
$config = [
    'DB_HOST' => $_ENV['DB_HOST'] ?? 'localhost',
    'DB_PORT' => $_ENV['DB_PORT'] ?? '5432',
    'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? 'wordsearch_dev',
    'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? 'wordsearch_dev_user',
    'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
    'JWT_SECRET' => $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-this-in-production',
    'JWT_EXPIRY' => $_ENV['JWT_EXPIRY'] ?? '3600',
    'SESSION_SECURE' => $_ENV['SESSION_SECURE'] ?? 'true',
    'SESSION_HTTP_ONLY' => $_ENV['SESSION_HTTP_ONLY'] ?? 'true',
    'SESSION_SAME_SITE' => $_ENV['SESSION_SAME_SITE'] ?? 'Strict',
    'game' => [
        'directions' => [
            'horizontal' => [0, 1],
            'vertical' => [1, 0],
            'diagonal_down' => [1, 1],
            'diagonal_up' => [-1, 1]
        ],
        'maxWordLen' => 15
    ]
];

echo "Testing puzzle generation...\n";

try {
    // Test database connection
    $db = new PDO(
        'pgsql:host=' . $config['DB_HOST'] . ';port=' . $config['DB_PORT'] . ';dbname=' . $config['DB_DATABASE'],
        $config['DB_USERNAME'],
        $config['DB_PASSWORD']
    );
    echo "✓ Database connection successful\n";
    
    // Test puzzle generator
    $generator = new \App\Services\PuzzleGenerator($config);
    $words = ['TEST', 'PUZZLE', 'GENERATION'];
    $options = [
        'size' => 10,
        'diagonals' => false,
        'reverse' => false
    ];
    
    $puzzle = $generator->generate($words, $options);
    echo "✓ Puzzle generated successfully\n";
    echo "  - Grid size: " . $puzzle['size'] . "x" . $puzzle['size'] . "\n";
    echo "  - Words: " . implode(', ', $puzzle['words']) . "\n";
    echo "  - Placed words: " . count($puzzle['placed']) . "\n";
    
    // Test theme service
    $themeService = new \App\Services\ThemeService($config);
    $themes = $themeService->getAvailableThemes();
    echo "✓ Themes loaded: " . implode(', ', array_keys($themes)) . "\n";
    
    echo "\n🎉 All tests passed! Puzzle generation should work now.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
