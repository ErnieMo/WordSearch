<?php

declare(strict_types=1);

namespace Sudoku\Controllers;

use Sudoku\Core\BaseController;
use Sudoku\Services\ThemeService;
use Exception;

/**
 * Theme Controller for WordSearch
 * 
 * Handles theme-related API endpoints.
 * 
 * @author WordSearch Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

class ThemeController extends BaseController
{
    private ThemeService $theme_service;

    public function __construct()
    {
        $database_service = new \Sudoku\Services\DatabaseService();
        $session_service = new \Sudoku\Services\SessionService($database_service, new \Sudoku\Services\LoggingService());
        parent::__construct($database_service, $session_service);
        $this->theme_service = new ThemeService();
    }

    /**
     * Get all available themes
     */
    public function getThemes(): void
    {
        try {
            $themes = $this->theme_service->getAvailableThemes();
            
            // Calculate stats
            $stats = [
                'total_themes' => count($themes),
                'total_words' => array_sum(array_column($themes, 'word_count'))
            ];

            $this->sendJsonResponse([
                'success' => true,
                'themes' => $themes,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to load themes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get words for a specific theme
     */
    public function getThemeWords(string $theme_id): void
    {
        try {
            if (!$this->theme_service->themeExists($theme_id)) {
                $this->sendErrorResponse('Theme not found', 404);
                return;
            }

            $words = $this->theme_service->getThemeWords($theme_id);
            $theme_info = $this->theme_service->getThemeInfo($theme_id);

            $this->sendJsonResponse([
                'success' => true,
                'theme' => $theme_info,
                'words' => $words
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to load theme words: ' . $e->getMessage(), 500);
        }
    }
}


