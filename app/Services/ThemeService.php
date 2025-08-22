<?php

declare(strict_types=1);

namespace App\Services;

class ThemeService
{
    private array $config;
    private string $themesPath;

    public function __construct(array $config)
    {
        $this->config = $config;
        // Use the project root directory to find themes
        $this->themesPath = __DIR__ . '/../../resources/themes';
    }

    public function getAvailableThemes(): array
    {
        $themes = [];
        $themeFiles = glob($this->themesPath . '/*.json');
        
        foreach ($themeFiles as $themeFile) {
            $themeName = basename($themeFile, '.json');
            $themeData = $this->loadTheme($themeName);
            if ($themeData) {
                $themes[$themeName] = $themeData;
            }
        }
        
        return $themes;
    }

    public function loadTheme(string $themeName): ?array
    {
        $themeFile = $this->themesPath . '/' . $themeName . '.json';
        
        if (!file_exists($themeFile)) {
            return null;
        }
        
        $content = file_get_contents($themeFile);
        if ($content === false) {
            return null;
        }
        
        $theme = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $theme;
    }

    public function getThemeWords(string $themeName): array
    {
        $theme = $this->loadTheme($themeName);
        return $theme['words'] ?? [];
    }

    public function getDefaultTheme(): string
    {
        return 'technology';
    }
}
