<?php

declare(strict_types=1);

namespace Sudoku\Services;

/**
 * Sudoku Game - Sudoku Service
 * 
 * Handles Sudoku puzzle generation, validation, and game logic.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

// Enable error logging for debugging
if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
// error_log("\n\n" . __FILE__ . PHP_EOL, 3, __DIR__ . '/../../../../Logs/included_files.log');
}

/**
 * Sudoku service for puzzle generation and validation
 */
class SudokuService
{
    private array $difficulty_levels = [
        'easy' => 25,      // 56 filled cells (good starting point)
        'medium' => 35,     // 46 filled cells (moderate challenge)
        'hard' => 45,       // 36 filled cells (challenging)
        'expert' => 55      // 26 filled cells (very difficult)
    ];

    /**
     * Generate a new Sudoku puzzle
     */
    public function generatePuzzle(string $difficulty = 'medium'): array
    {
        $cells_to_remove = $this->difficulty_levels[$difficulty] ?? 35;
        
        // Generate a complete Sudoku solution using a more reliable method
        $solution = $this->generateCompleteSolutionReliable();
        
        // Create puzzle by removing cells
        $puzzle = $this->createPuzzle($solution, $cells_to_remove);
        
        // Debug: Count filled cells
        $filled_cells = 0;
        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 9; $j++) {
                if ($puzzle[$i][$j] !== 0) {
                    $filled_cells++;
                }
            }
        }
        
    // error_log("\n\n" . "Generated puzzle for difficulty '$difficulty': $filled_cells filled cells, " . (81 - $filled_cells) . " empty cells");
        
        return [
            'original' => $puzzle,
            'puzzle' => $puzzle,
            'solution' => $solution,
            'difficulty' => $difficulty
        ];
    }

    /**
     * Generate a complete Sudoku solution using a more reliable method
     */
    private function generateCompleteSolutionReliable(): array
    {
        // Start with a valid base pattern
        $board = $this->createBasePattern();
        
        // Apply random transformations to create a unique puzzle
        $this->randomizeBoard($board);
        
        return $board;
    }

    /**
     * Create a base Sudoku pattern
     */
    private function createBasePattern(): array
    {
        // Start with a known valid Sudoku solution
        $board = [
            [5, 3, 4, 6, 7, 8, 9, 1, 2],
            [6, 7, 2, 1, 9, 5, 3, 4, 8],
            [1, 9, 8, 3, 4, 2, 5, 6, 7],
            [8, 5, 9, 7, 6, 1, 4, 2, 3],
            [4, 2, 6, 8, 5, 3, 7, 9, 1],
            [7, 1, 3, 9, 2, 4, 8, 5, 6],
            [9, 6, 1, 5, 3, 7, 2, 8, 4],
            [2, 8, 7, 4, 1, 9, 6, 3, 5],
            [3, 4, 5, 2, 8, 6, 1, 7, 9]
        ];
        
        return $board;
    }

    /**
     * Randomize the board using valid Sudoku transformations
     */
    private function randomizeBoard(array &$board): void
    {
        // Apply random row swaps within the same 3-row blocks
        for ($block = 0; $block < 3; $block++) {
            $start_row = $block * 3;
            $this->swapRows($board, $start_row, $start_row + 1);
            $this->swapRows($board, $start_row + 1, $start_row + 2);
            $this->swapRows($board, $start_row, $start_row + 2);
        }
        
        // Apply random column swaps within the same 3-column blocks
        for ($block = 0; $block < 3; $block++) {
            $start_col = $block * 3;
            $this->swapColumns($board, $start_col, $start_col + 1);
            $this->swapColumns($board, $start_col + 1, $start_col + 2);
            $this->swapColumns($board, $start_col, $start_col + 2);
        }
        
        // Apply random number substitutions
        $this->substituteNumbers($board);
    }

    /**
     * Swap two rows
     */
    private function swapRows(array &$board, int $row1, int $row2): void
    {
        if (rand(0, 1)) { // 50% chance to swap
            $temp = $board[$row1];
            $board[$row1] = $board[$row2];
            $board[$row2] = $temp;
        }
    }

    /**
     * Swap two columns
     */
    private function swapColumns(array &$board, int $col1, int $col2): void
    {
        if (rand(0, 1)) { // 50% chance to swap
            for ($row = 0; $row < 9; $row++) {
                $temp = $board[$row][$col1];
                $board[$row][$col1] = $board[$row][$col2];
                $board[$row][$col2] = $temp;
            }
        }
    }

    /**
     * Substitute numbers (e.g., swap all 1s with 2s, etc.)
     */
    private function substituteNumbers(array &$board): void
    {
        $substitutions = [
            [1, 2], [3, 4], [5, 6], [7, 8], [1, 3], [2, 4], [5, 7], [6, 8]
        ];
        
        foreach ($substitutions as $sub) {
            if (rand(0, 1)) { // 50% chance to apply each substitution
                for ($row = 0; $row < 9; $row++) {
                    for ($col = 0; $col < 9; $col++) {
                        if ($board[$row][$col] === $sub[0]) {
                            $board[$row][$col] = $sub[1];
                        } elseif ($board[$row][$col] === $sub[1]) {
                            $board[$row][$col] = $sub[0];
                        }
                    }
                }
            }
        }
    }

    /**
     * Create puzzle by removing cells from solution
     */
    private function createPuzzle(array $solution, int $cells_to_remove): array
    {
        $puzzle = array_map(function($row) {
            return $row;
        }, $solution);
        
        $positions = [];
        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 9; $j++) {
                $positions[] = [$i, $j];
            }
        }
        
        shuffle($positions);
        
        for ($i = 0; $i < $cells_to_remove; $i++) {
            $pos = $positions[$i];
            $puzzle[$pos[0]][$pos[1]] = 0;
        }
        
        return $puzzle;
    }

    /**
     * Check if a move is valid
     */
    public function isValidMove(array $board, int $row, int $col, int $value): bool
    {
        // Check row
        for ($j = 0; $j < 9; $j++) {
            if ($j !== $col && $board[$row][$j] === $value) {
                return false;
            }
        }
        
        // Check column
        for ($i = 0; $i < 9; $i++) {
            if ($i !== $row && $board[$i][$col] === $value) {
                return false;
            }
        }
        
        // Check 3x3 box
        $box_row = 3 * intval($row / 3);
        $box_col = 3 * intval($col / 3);
        
        for ($i = $box_row; $i < $box_row + 3; $i++) {
            for ($j = $box_col; $j < $box_col + 3; $j++) {
                if (($i !== $row || $j !== $col) && $board[$i][$j] === $value) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Check if board is complete
     */
    public function isBoardComplete(array $board): bool
    {
        // Check if all cells are filled
        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 9; $j++) {
                if ($board[$i][$j] === 0) {
                    return false;
                }
            }
        }
        
        // Check if solution is valid
        return $this->isValidSolution($board);
    }

    /**
     * Check if solution is valid
     */
    private function isValidSolution(array $board): bool
    {
        // Check rows
        for ($i = 0; $i < 9; $i++) {
            $row = $board[$i];
            if (count($row) !== 9 || count(array_unique($row)) !== 9) {
                return false;
            }
        }
        
        // Check columns
        for ($j = 0; $j < 9; $j++) {
            $col = [];
            for ($i = 0; $i < 9; $i++) {
                $col[] = $board[$i][$j];
            }
            if (count($col) !== 9 || count(array_unique($col)) !== 9) {
                return false;
            }
        }
        
        // Check 3x3 boxes
        for ($box_row = 0; $box_row < 9; $box_row += 3) {
            for ($box_col = 0; $box_col < 9; $box_col += 3) {
                $box = [];
                for ($i = $box_row; $i < $box_row + 3; $i++) {
                    for ($j = $box_col; $j < $box_col + 3; $j++) {
                        $box[] = $board[$i][$j];
                    }
                }
                if (count($box) !== 9 || count(array_unique($box)) !== 9) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Get difficulty levels
     */
    public function getDifficultyLevels(): array
    {
        return array_keys($this->difficulty_levels);
    }

    /**
     * Get cells to remove for difficulty
     */
    public function getCellsToRemove(string $difficulty): int
    {
        return $this->difficulty_levels[$difficulty] ?? 40;
    }

    /**
     * Get a hint for the current board
     */
    public function getHint(array $board, array $solution): ?array
    {
        // Find an empty cell and provide its solution
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($board[$row][$col] === 0) {
                    return [
                        'row' => $row,
                        'col' => $col,
                        'value' => $solution[$row][$col]
                    ];
                }
            }
        }
        
        return null; // No empty cells found
    }
} 