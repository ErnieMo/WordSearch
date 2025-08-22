<?php

declare(strict_types=1);

namespace App\Services;

class ThemeService
{
    private string $themesPath;
    private array $themes = [];

    public function __construct()
    {
        $this->themesPath = __DIR__ . '/../../resources/themes/';
        $this->loadThemes();
    }

    private function loadThemes(): void
    {
        if (!is_dir($this->themesPath)) {
            throw new \RuntimeException("Themes directory not found: {$this->themesPath}");
        }

        $themeFiles = glob($this->themesPath . '*.json');
        
        foreach ($themeFiles as $themeFile) {
            $themeName = basename($themeFile, '.json');
            $themeData = json_decode(file_get_contents($themeFile), true);
            
            if ($themeData && isset($themeData['words'])) {
                $this->themes[$themeName] = [
                    'name' => $themeData['name'] ?? ucfirst($themeName),
                    'description' => $themeData['description'] ?? '',
                    'words' => $themeData['words'],
                    'difficulty' => $themeData['difficulty'] ?? 'medium'
                ];
            }
        }
    }

    public function getAvailableThemes(): array
    {
        $availableThemes = [];
        
        foreach ($this->themes as $key => $theme) {
            $availableThemes[] = [
                'id' => $key,
                'name' => $theme['name'],
                'description' => $theme['description'],
                'word_count' => count($theme['words']),
                'difficulty' => $theme['difficulty']
            ];
        }

        return $availableThemes;
    }

    public function getThemeWords(string $themeId): array
    {
        if (!isset($this->themes[$themeId])) {
            throw new \InvalidArgumentException("Theme '{$themeId}' not found");
        }

        return $this->themes[$themeId]['words'];
    }

    public function getThemeInfo(string $themeId): ?array
    {
        if (!isset($this->themes[$themeId])) {
            return null;
        }

        $theme = $this->themes[$themeId];
        return [
            'id' => $themeId,
            'name' => $theme['name'],
            'description' => $theme['description'],
            'word_count' => count($theme['words']),
            'difficulty' => $theme['difficulty']
        ];
    }

    public function themeExists(string $themeId): bool
    {
        return isset($this->themes[$themeId]);
    }

    public function getRandomWords(string $themeId, int $count): array
    {
        $words = $this->getThemeWords($themeId);
        
        if (count($words) <= $count) {
            return $words;
        }

        // Shuffle and take first $count words
        shuffle($words);
        return array_slice($words, 0, $count);
    }

    public function getWordsByDifficulty(string $themeId, string $difficulty): array
    {
        $words = $this->getThemeWords($themeId);
        
        // Filter words by difficulty level
        switch ($difficulty) {
            case 'easy':
                return array_filter($words, fn($word) => strlen($word) <= 6);
            case 'medium':
                return array_filter($words, fn($word) => strlen($word) <= 10);
            case 'hard':
                return $words; // All words for hard difficulty
            default:
                return $words;
        }
    }

    public function validateTheme(string $themeId): bool
    {
        if (!isset($this->themes[$themeId])) {
            return false;
        }

        $theme = $this->themes[$themeId];
        
        // Check if theme has required fields
        if (!isset($theme['words']) || !is_array($theme['words'])) {
            return false;
        }

        // Check if words array is not empty
        if (empty($theme['words'])) {
            return false;
        }

        // Check if all words are strings
        foreach ($theme['words'] as $word) {
            if (!is_string($word) || empty(trim($word))) {
                return false;
            }
        }

        return true;
    }

    public function getThemeStats(): array
    {
        $stats = [
            'total_themes' => count($this->themes),
            'total_words' => 0,
            'themes' => []
        ];

        foreach ($this->themes as $themeId => $theme) {
            $wordCount = count($theme['words']);
            $stats['total_words'] += $wordCount;
            
            $stats['themes'][] = [
                'id' => $themeId,
                'name' => $theme['name'],
                'word_count' => $wordCount,
                'difficulty' => $theme['difficulty']
            ];
        }

        return $stats;
    }
}
