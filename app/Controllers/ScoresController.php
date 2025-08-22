<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseService;
use App\Services\AuthService;

class ScoresController
{
    private DatabaseService $dbService;
    private AuthService $authService;
    private array $config;

    public function __construct(DatabaseService $dbService, array $config)
    {
        $this->dbService = $dbService;
        $this->authService = new AuthService($dbService, $config);
        $this->config = $config;
    }

    public function getScores(): string
    {
        try {
            $theme = $_GET['theme'] ?? '';
            $difficulty = $_GET['difficulty'] ?? '';
            $timeFilter = $_GET['time'] ?? '';

            // Build where clause
            $where = '1=1';
            $params = [];

            if ($theme) {
                $where .= ' AND theme = :theme';
                $params['theme'] = $theme;
            }

            if ($difficulty) {
                $where .= ' AND difficulty = :difficulty';
                $params['difficulty'] = $difficulty;
            }

            if ($timeFilter) {
                switch ($timeFilter) {
                    case 'today':
                        $where .= ' AND DATE(created_at) = CURRENT_DATE';
                        break;
                    case 'week':
                        $where .= ' AND created_at >= CURRENT_DATE - INTERVAL \'7 days\'';
                        break;
                    case 'month':
                        $where .= ' AND created_at >= CURRENT_DATE - INTERVAL \'30 days\'';
                        break;
                }
            }

            // Get scores ordered by words found and time
            $scores = $this->dbService->findMany(
                'scores',
                $where,
                $params,
                'words_found DESC, elapsed_time ASC',
                100
            );

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'scores' => $scores
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Failed to load scores'
            ]);
        }
    }

    public function getMyScores(): string
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                throw new \Exception('Authentication required');
            }

            $userData = $this->authService->verifyToken($token);
            if (!$userData) {
                throw new \Exception('Invalid or expired token');
            }

            $limit = (int)($_GET['limit'] ?? 50);
            $scores = $this->dbService->findMany(
                'scores',
                'user_id = :user_id',
                ['user_id' => $userData['user_id']],
                'created_at DESC',
                $limit
            );

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'scores' => $scores
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getStats(): string
    {
        try {
            // Get overall statistics
            $totalPlayers = $this->dbService->count('users', 'is_active = true');
            $totalGames = $this->dbService->count('scores');
            $totalWords = $this->dbService->query(
                'SELECT COALESCE(SUM(words_found), 0) as total FROM scores'
            )[0]['total'] ?? 0;

            // Get average time
            $avgTimeResult = $this->dbService->query(
                'SELECT COALESCE(AVG(elapsed_time), 0) as avg_time FROM scores WHERE elapsed_time > 0'
            );
            $avgTime = (int)($avgTimeResult[0]['avg_time'] ?? 0);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'stats' => [
                    'total_players' => $totalPlayers,
                    'total_games' => $totalGames,
                    'total_words' => $totalWords,
                    'avg_time' => $avgTime
                ]
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            return json_encode([
                'success' => false,
                'message' => 'Failed to load statistics'
            ]);
        }
    }

    public function getMyStats(): string
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                throw new \Exception('Authentication required');
            }

            $userData = $this->authService->verifyToken($token);
            if (!$userData) {
                throw new \Exception('Invalid or expired token');
            }

            // Get user statistics
            $totalGames = $this->dbService->count('scores', 'user_id = :user_id', ['user_id' => $userData['user_id']]);
            
            $totalWordsResult = $this->dbService->query(
                'SELECT COALESCE(SUM(words_found), 0) as total FROM scores WHERE user_id = :user_id',
                ['user_id' => $userData['user_id']]
            );
            $totalWords = $totalWordsResult[0]['total'] ?? 0;

            $avgTimeResult = $this->dbService->query(
                'SELECT COALESCE(AVG(elapsed_time), 0) as avg_time FROM scores WHERE user_id = :user_id AND elapsed_time > 0',
                ['user_id' => $userData['user_id']]
            );
            $avgTime = (int)($avgTimeResult[0]['avg_time'] ?? 0);

            $totalHintsResult = $this->dbService->query(
                'SELECT COALESCE(SUM(hints_used), 0) as total FROM scores WHERE user_id = :user_id',
                ['user_id' => $userData['user_id']]
            );
            $totalHints = $totalHintsResult[0]['total'] ?? 0;

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'stats' => [
                    'total_games' => $totalGames,
                    'total_words' => $totalWords,
                    'avg_time' => $avgTime,
                    'total_hints' => $totalHints
                ]
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getAuthToken(): ?string
    {
        // Check Authorization header first
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        // Check cookie
        return $_COOKIE['auth_token'] ?? null;
    }
}
