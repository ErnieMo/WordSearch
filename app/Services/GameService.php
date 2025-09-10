<?php

declare(strict_types=1);

namespace App\Services;

class GameService
{
    private DatabaseService $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new game in the database
     */
    public function createGame(array $gameData): int
    {
        $data = [
            'user_id' => $gameData['user_id'] ?? null,
            'puzzle_id' => $gameData['puzzle_id'],
            'theme' => $gameData['theme'],
            'difficulty' => $gameData['difficulty'],
            'grid_size' => $gameData['grid_size'] ?? 15,
            'puzzle_data' => json_encode($gameData['puzzle_data'] ?? []),
            'start_time' => date('Y-m-d H:i:s'),
            'words_found' => 0,
            'total_words' => $gameData['total_words'],
            'status' => 'active'
        ];

        error_log("GameService: About to insert game data: " . json_encode($data));
        $result = $this->db->insert('wordsearch_games', $data);
        error_log("GameService: Insert result: " . $result, 3, '/var/www/html/Logs/wordsearch_debug.log');
        return $result;
    }

    /**
     * Get a game by ID
     */
    public function getGame(int $gameId): ?array
    {
        $game = $this->db->fetchOne(
            "SELECT * FROM wordsearch_games WHERE id = :id",
            ['id' => $gameId]
        );

        if ($game && isset($game['puzzle_data'])) {
            $game['puzzle_data'] = json_decode($game['puzzle_data'], true);
        }

        return $game;
    }

    /**
     * Get a game by puzzle ID
     */
    public function getGameByPuzzleId(string $puzzleId): ?array
    {
        $game = $this->db->fetchOne(
            "SELECT * FROM wordsearch_games WHERE puzzle_id = :puzzle_id",
            ['puzzle_id' => $puzzleId]
        );

        if ($game && isset($game['puzzle_data'])) {
            $game['puzzle_data'] = json_decode($game['puzzle_data'], true);
        }

        return $game;
    }

    /**
     * Get active game for a user
     */
    public function getActiveGame(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM wordsearch_games WHERE user_id = :user_id AND status = 'active' ORDER BY start_time DESC LIMIT 1",
            ['user_id' => $userId]
        );
    }

    /**
     * Update game progress
     */
    public function updateGameProgress(int $gameId, array $data): bool
    {
        $allowedFields = ['words_found', 'hints_used', 'status', 'words_found_data'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return false;
        }

        if (isset($data['words_found_data'])) {
            $updateData['words_found_data'] = json_encode($data['words_found_data']);
        }

        $this->db->update('wordsearch_games', $updateData, ['id' => $gameId]);
        return true;
    }

    /**
     * Complete a game
     */
    public function completeGame(int $gameId, int $elapsedTime): bool
    {
        $data = [
            'status' => 'completed',
            'end_time' => date('Y-m-d H:i:s'),
            'elapsed_time' => $elapsedTime
        ];

        $this->db->update('wordsearch_games', $data, ['id' => $gameId]);
        return true;
    }

    /**
     * Update a game with completion data
     */
    public function updateGame(int $gameId, array $data): bool
    {
        $allowedFields = [
            'user_id', 'completion_time', 'hints_used', 'completed_at', 
            'status', 'end_time', 'elapsed_time', 'words_found', 'words_found_data'
        ];
        
        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            error_log("GameService::updateGame: No update data provided", 3, '/var/www/html/Logs/wordsearch_debug.log');
            return false;
        }

        // JSON encode words_found_data if present
        if (isset($updateData['words_found_data'])) {
            $updateData['words_found_data'] = json_encode($updateData['words_found_data']);
        }
        
        error_log("GameService::updateGame: Updating game $gameId with data: " . json_encode($updateData));
        
        $result = $this->db->update('wordsearch_games', $updateData, ['id' => $gameId]);
        
        error_log("GameService::updateGame: Update result: " . ($result ? 'success' : 'failed'));
        
        // Convert integer result to boolean (0 = false, >0 = true)
        return $result > 0;
    }

    /**
     * Get user's game history
     */
    public function getUserGameHistory(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM wordsearch_games WHERE user_id = :user_id ORDER BY start_time DESC LIMIT :limit",
            ['user_id' => $userId, 'limit' => $limit]
        );
    }

    /**
     * Get game statistics for a user
     */
    public function getUserGameStats(int $userId): array
    {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_games,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_games,
                AVG(CASE WHEN status = 'completed' THEN elapsed_time END) as avg_time,
                SUM(CASE WHEN status = 'completed' THEN words_found END) as total_words_found,
                SUM(CASE WHEN status = 'completed' THEN hints_used END) as total_hints_used
            FROM wordsearch_games 
            WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        return $stats ?? [
            'total_games' => 0,
            'completed_games' => 0,
            'avg_time' => 0,
            'total_words_found' => 0,
            'total_hints_used' => 0
        ];
    }

    /**
     * Get leaderboard for a specific theme and difficulty
     */
    public function getLeaderboard(string $theme, string $difficulty, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT 
                g.id,
                g.elapsed_time,
                g.words_found,
                g.hints_used,
                g.start_time,
                u.username,
                u.first_name,
                u.last_name
            FROM wordsearch_games g
            JOIN users u ON g.user_id = u.id
            WHERE g.theme = :theme 
            AND g.difficulty = :difficulty 
            AND g.status = 'completed'
            ORDER BY g.elapsed_time ASC, g.words_found DESC
            LIMIT :limit",
            ['theme' => $theme, 'difficulty' => $difficulty, 'limit' => $limit]
        );
    }
}
