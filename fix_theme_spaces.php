<?php
require_once 'vendor/autoload.php';

echo "=== Fixing Theme Word Spaces ===\n";

$themesDir = __DIR__ . '/resources/themes/';
$themeFiles = glob($themesDir . '*.json');

foreach ($themeFiles as $themeFile) {
    $themeName = basename($themeFile);
    echo "Processing: $themeName\n";
    
    $themeData = json_decode(file_get_contents($themeFile), true);
    
    if (!$themeData || !isset($themeData['words'])) {
        echo "  ❌ Invalid theme file\n";
        continue;
    }
    
    $originalWords = $themeData['words'];
    $fixedWords = [];
    $fixedCount = 0;
    
    foreach ($originalWords as $word) {
        $originalWord = $word;
        $fixedWord = str_replace(' ', '', $word);
        
        if ($originalWord !== $fixedWord) {
            echo "  🔧 Fixed: \"$originalWord\" -> \"$fixedWord\"\n";
            $fixedCount++;
        }
        
        $fixedWords[] = $fixedWord;
    }
    
    if ($fixedCount > 0) {
        $themeData['words'] = $fixedWords;
        
        // Write back to file
        $jsonContent = json_encode($themeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($themeFile, $jsonContent);
        
        echo "  ✅ Fixed $fixedCount words in $themeName\n";
    } else {
        echo "  ✅ No spaces found in $themeName\n";
    }
    
    echo "\n";
}

echo "=== Theme Space Fixing Complete ===\n";
