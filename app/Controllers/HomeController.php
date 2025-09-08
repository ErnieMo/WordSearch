<?php

declare(strict_types=1);

namespace Sudoku\Controllers;

/**
 * Sudoku Game - Home Controller
 * 
 * Handles the main landing page and dashboard.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

use Sudoku\Core\BaseController;

/**
 * Home controller
 */
class HomeController extends BaseController
{
    /**
     * Show home page
     */
    public function index(): void
    {
        $user = $this->session_service->getCurrentUser();
        $flash_messages = $this->session_service->getAllFlash();
        
        $this->render('home/index', [
            'user' => $user,
            'flash_messages' => $flash_messages
        ]);
    }

    /**
     * Show create Sudoku page
     */
    public function showCreate(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $flash_messages = $this->session_service->getAllFlash();
        
        $this->render('home/create', [
            'user' => $user,
            'flash_messages' => $flash_messages
        ]);
    }

    /**
     * Show dashboard
     */
    public function dashboard(): void
    {
        $user = $this->getCurrentUser();

        // Get user statistics - use 'id' instead of 'user_id'
        $user_id = $user['id'] ?? $user['user_id'];
        $stats = $this->getUserStats($user_id);
        $recent_games = $this->getRecentGames($user_id);
        $flash_messages = $this->session_service->getAllFlash();
        
        $this->render('home/dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recent_games' => $recent_games,
            'flash_messages' => $flash_messages
        ]);
    }

    /**
     * Show how to play guide (AJAX endpoint)
     */
    public function howToPlay(): void
    {
        // This is an AJAX endpoint that returns the how-to-play content
        // We don't use the shared layout, just the content
        include __DIR__ . '/../../resources/views/how-to-play.php';
    }

    /**
     * Get user statistics
     */
    private function getUserStats(int $user_id): array
    {
        // Initialize default values
        $stats = [
            'total_games' => 0,
            'completed_games' => 0,
            'completion_rate' => 0,
            'best_time' => null,
            'avg_time' => null,
            'total_hints' => 0,
            'total_errors' => 0
        ];

        try {
            // Total games played
            $total_games = $this->database_service->count('sudoku_games', ['user_id' => $user_id]);
            $stats['total_games'] = $total_games;

            // Completed games
            $completed_games = $this->database_service->count('sudoku_games', ['user_id' => $user_id, 'status' => 'completed']);
            $stats['completed_games'] = $completed_games;

            // Calculate completion rate
            $stats['completion_rate'] = $total_games > 0 ? round(($completed_games / $total_games) * 100, 1) : 0;

            // Best time
            try {
                $best_time = $this->database_service->findOne(
                    'sudoku_games',
                    ['user_id' => $user_id, 'status' => 'completed'],
                    ['order_by' => 'elapsed_time ASC']
                );
                $stats['best_time'] = $best_time ? $best_time['elapsed_time'] : null;
            } catch (\Exception $e) {
                // Best time not available
            }

            // Average time
            try {
                $avg_time_result = $this->database_service->query(
                    "SELECT AVG(elapsed_time) as avg_time FROM sudoku_games WHERE user_id = ? AND status = 'completed'",
                    [$user_id]
                );
                $stats['avg_time'] = $avg_time_result ? $avg_time_result[0]['avg_time'] : null;
            } catch (\Exception $e) {
                // Average time not available
            }

            // Total hints and errors from scores table
            try {
                $stats_result = $this->database_service->query(
                    "SELECT SUM(hint_count) as total_hints, SUM(error_count) as total_errors FROM sudoku_scores WHERE user_id = ?",
                    [$user_id]
                );

                $total_hints = $stats_result ? ($stats_result[0]['total_hints'] ?? 0) : 0;
                $total_errors = $stats_result ? ($stats_result[0]['total_errors'] ?? 0) : 0;

                $stats['total_hints'] = $total_hints;
                $stats['total_errors'] = $total_errors;
            } catch (\Exception $e) {
                // Hints/errors stats not available
            }

            return $stats;
        } catch (\Exception $e) {
            return $stats; // Return the stats with whatever we managed to calculate
        }
    }

    /**
     * Get recent games with complete metrics
     */
    private function getRecentGames(int $user_id): array
    {
        try {
            // Fetch games with errors and hints data
            $sql = "SELECT 
                        g.*,
                        COALESCE(s.error_count, 0) as errors_count,
                        COALESCE(s.hint_count, 0) as hints_used
                    FROM sudoku_games g
                    LEFT JOIN sudoku_scores s ON g.id = s.game_id
                    WHERE g.user_id = ?
                    ORDER BY g.id DESC
                    LIMIT 50";
            
            return $this->database_service->query($sql, [$user_id]);
        } catch (\Exception $e) {
            // Fallback to basic games data if the join fails
            try {
                return $this->database_service->find(
                    'sudoku_games',
                    ['user_id' => $user_id],
                    [
                        'order_by' => 'id DESC',
                        'limit' => 50
                    ]
                );
            } catch (\Exception $e2) {
                return [];
            }
        }
    }
} 