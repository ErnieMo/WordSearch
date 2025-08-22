<?php

declare(strict_types=1);

// Simple test script to verify application components
echo "Word Search Application Test\n";
echo "===========================\n\n";

// Test 1: Check if Composer autoloader works
echo "1. Testing Composer autoloader...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Composer autoloader loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Composer autoloader failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if configuration loads
echo "\n2. Testing configuration...\n";
try {
    $config = require __DIR__ . '/app/config.php';
    echo "✓ Configuration loaded successfully\n";
    echo "  - Game directions: " . count($config['game']['directions']) . " directions\n";
    echo "  - Alphabet: " . strlen($config['game']['alphabet']) . " characters\n";
    echo "  - Max word length: " . $config['game']['maxWordLen'] . "\n";
} catch (Exception $e) {
    echo "✗ Configuration failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if theme service works
echo "\n3. Testing theme service...\n";
try {
    $themeService = new App\Services\ThemeService();
    $themes = $themeService->getAvailableThemes();
    echo "✓ Theme service working\n";
    echo "  - Available themes: " . count($themes) . "\n";
    foreach ($themes as $theme) {
        echo "    * {$theme['name']} ({$theme['word_count']} words)\n";
    }
} catch (Exception $e) {
    echo "✗ Theme service failed: " . $e->getMessage() . "\n";
}

// Test 4: Check if puzzle generator works
echo "\n4. Testing puzzle generator...\n";
try {
    $generator = new App\Services\PuzzleGenerator();
    $words = ['TEST', 'WORD', 'PUZZLE', 'GAME'];
    $options = ['size' => 10, 'diagonals' => false, 'reverse' => false];
    
    $puzzle = $generator->generatePuzzle($words, $options);
    echo "✓ Puzzle generator working\n";
    echo "  - Generated puzzle ID: " . $puzzle['id'] . "\n";
    echo "  - Grid size: " . $puzzle['size'] . "x" . $puzzle['size'] . "\n";
    echo "  - Words placed: " . count($puzzle['placed_words']) . "\n";
    echo "  - Words failed: " . count($puzzle['failed_words']) . "\n";
} catch (Exception $e) {
    echo "✗ Puzzle generator failed: " . $e->getMessage() . "\n";
}

// Test 5: Check if puzzle store works
echo "\n5. Testing puzzle store...\n";
try {
    $store = new App\Services\PuzzleStore();
    echo "✓ Puzzle store working\n";
    echo "  - Storage available: " . ($store->isAvailable() ? 'Yes' : 'No') . "\n";
    
    $info = $store->getStorageInfo();
    echo "  - Storage path: " . $info['path'] . "\n";
    echo "  - Writable: " . ($info['writable'] ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "✗ Puzzle store failed: " . $e->getMessage() . "\n";
}

// Test 6: Check directory structure
echo "\n6. Testing directory structure...\n";
$requiredDirs = [
    'app/Controllers',
    'app/Services', 
    'app/Http',
    'app/Utils',
    'public/assets/css',
    'public/assets/js',
    'public/views',
    'resources/themes',
    'storage/logs',
    'storage/puzzles',
    'storage/cache'
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "✓ {$dir}\n";
    } else {
        echo "✗ {$dir} (missing)\n";
    }
}

// Test 7: Check theme files
echo "\n7. Testing theme files...\n";
$themeFiles = glob('resources/themes/*.json');
if (empty($themeFiles)) {
    echo "✗ No theme files found\n";
} else {
    echo "✓ Found " . count($themeFiles) . " theme files:\n";
    foreach ($themeFiles as $file) {
        $themeName = basename($file, '.json');
        echo "  * {$themeName}\n";
    }
}

echo "\n" . str_repeat("=", 40) . "\n";
echo "Test completed!\n";

if (isset($config) && isset($themes) && isset($puzzle) && isset($store)) {
    echo "✓ All core components are working correctly!\n";
    echo "The Word Search application is ready to use.\n";
} else {
    echo "⚠ Some components have issues. Check the output above.\n";
}

echo "\nNext steps:\n";
echo "1. Configure your .env file with database credentials\n";
echo "2. Run: php setup_database.php\n";
echo "3. Configure your web server to point to the public/ directory\n";
echo "4. Visit the application in your browser\n";
