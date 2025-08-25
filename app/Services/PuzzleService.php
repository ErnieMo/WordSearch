<?php

declare(strict_types=1);

namespace App\Services;

class PuzzleService
{
    private DatabaseService $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * Save a puzzle to the database
     */
    public function savePuzzle(array $puzzleData): string
    {
        $data = [
            'puzzle_id' => $puzzleData['id'],
            'user_id' => $puzzleData['user_id'] ?? null,
            'theme' => $puzzleData['theme'] ?? 'unknown',
            'difficulty' => $puzzleData['difficulty'] ?? 'medium',
            'grid_size' => $puzzleData['size'],
            'words' => json_encode($puzzleData['words']),
            'grid' => json_encode($puzzleData['grid']),
            'placed_words' => json_encode($puzzleData['placed_words']),
            'seed' => $puzzleData['seed'] ?? null
        ];

        $this->db->insert('puzzles', $data);
        return $puzzleData['id'];
    }

    /**
     * Get a puzzle by ID from the database
     */
    public function getPuzzle(string $puzzleId): ?array
    {
        $puzzle = $this->db->fetchOne(
            "SELECT * FROM puzzles WHERE puzzle_id = :puzzle_id",
            ['puzzle_id' => $puzzleId]
        );

        if (!$puzzle) {
            return null;
        }

        // Decode JSON fields
        $puzzle['words'] = json_decode($puzzle['words'], true);
        $puzzle['grid'] = json_decode($puzzle['grid'], true);
        $puzzle['placed_words'] = json_decode($puzzle['placed_words'], true);

        return $puzzle;
    }

    /**
     * Delete a puzzle from the database
     */
    public function deletePuzzle(string $puzzleId): bool
    {
        $this->db->delete('puzzles', ['puzzle_id' => $puzzleId]);
        return true;
    }

    /**
     * Get puzzles by theme and difficulty
     */
    public function getPuzzlesByTheme(string $theme, string $difficulty, int $limit = 10): array
    {
        $puzzles = $this->db->fetchAll(
            "SELECT * FROM puzzles WHERE theme = :theme AND difficulty = :difficulty ORDER BY created_at DESC LIMIT :limit",
            ['theme' => $theme, 'difficulty' => $difficulty, 'limit' => $limit]
        );

        // Decode JSON fields for each puzzle
        foreach ($puzzles as &$puzzle) {
            $puzzle['words'] = json_decode($puzzle['words'], true);
            $puzzle['grid'] = json_decode($puzzle['grid'], true);
            $puzzle['placed_words'] = json_decode($puzzle['placed_words'], true);
        }

        return $puzzles;
    }

    /**
     * Get puzzle statistics
     */
    public function getPuzzleStats(): array
    {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_puzzles,
                COUNT(DISTINCT theme) as unique_themes,
                COUNT(DISTINCT difficulty) as unique_difficulties,
                AVG(grid_size) as avg_grid_size
            FROM puzzles"
        );

        return $stats ?? [
            'total_puzzles' => 0,
            'unique_themes' => 0,
            'unique_difficulties' => 0,
            'avg_grid_size' => 0
        ];
    }
}
