<?php

declare(strict_types=1);

namespace App\Services;

class PuzzleGenerator
{
    private array $config;
    private array $directions;
    private string $alphabet;
    private int $maxWordLen;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config.php';
        $this->directions = $this->config['game']['directions'];
        $this->alphabet = $this->config['game']['alphabet'];
        $this->maxWordLen = $this->config['game']['maxWordLen'];
    }

    public function generatePuzzle(array $words, array $options): array
    {
        $size = $options['size'] ?? 15;
        $diagonals = $options['diagonals'] ?? false;
        $reverse = $options['reverse'] ?? false;
        $seed = $options['seed'] ?? null;
        $targetWordCount = $options['word_count'] ?? count($words);

        if ($seed !== null) {
            mt_srand($seed);
        }

        // Filter and sort words by length
        $filteredWords = $this->filterWords($words, $size);
        $sortedWords = $this->sortWordsByLength($filteredWords);
        
        // Create a pool of available words for replacement
        $wordPool = $sortedWords;
        $usedWords = [];

        // Generate grid
        $grid = $this->createEmptyGrid($size);
        $placedWords = [];
        $failedWords = [];
        $attempts = 0;
        $maxAttempts = count($wordPool) * 2; // Limit attempts to avoid infinite loops

        // Try to place words until we reach target count or run out of attempts
        while (count($placedWords) < $targetWordCount && $attempts < $maxAttempts && !empty($wordPool)) {
            $attempts++;
            
            // Get next word from pool
            $word = array_shift($wordPool);
            $placed = $this->placeWord($grid, $word, $diagonals, $reverse);
            
            if ($placed) {
                $placedWords[] = $placed;
                $usedWords[] = $word;
            } else {
                $failedWords[] = $word;
                
                // Try to find a replacement word from the original word list
                $replacementWord = $this->findReplacementWord($word, $words, $usedWords, $size);
                if ($replacementWord) {
                    $wordPool[] = $replacementWord;
                }
            }
        }

        // Fill empty cells with random letters
        $this->fillEmptyCells($grid);

        // Generate unique puzzle ID
        $puzzleId = $this->generatePuzzleId();

        return [
            'id' => $puzzleId,
            'grid' => $grid,
            'words' => array_slice($usedWords, 0, $targetWordCount), // Return only the words we actually used
            'placed_words' => $placedWords,
            'failed_words' => $failedWords,
            'size' => $size,
            'options' => $options,
            'seed' => $seed
        ];
    }

    private function filterWords(array $words, int $gridSize): array
    {
        return array_filter($words, function($word) use ($gridSize) {
            $word = strtoupper(trim($word));
            return strlen($word) <= min($gridSize, $this->maxWordLen) && 
                   strlen($word) >= 3 && 
                   preg_match('/^[A-Z]+$/', $word);
        });
    }

    private function sortWordsByLength(array $words): array
    {
        usort($words, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        return $words;
    }

    private function createEmptyGrid(int $size): array
    {
        $grid = [];
        for ($r = 0; $r < $size; $r++) {
            $grid[$r] = [];
            for ($c = 0; $c < $size; $c++) {
                $grid[$r][$c] = '';
            }
        }
        return $grid;
    }

    private function placeWord(array &$grid, string $word, bool $diagonals, bool $reverse): ?array
    {
        $word = strtoupper(trim($word));
        $wordLen = strlen($word);
        $gridSize = count($grid);

        // Get available directions
        $availableDirections = $this->getAvailableDirections($diagonals);
        
        // Try multiple starting positions and directions
        $maxAttempts = $gridSize * $gridSize * count($availableDirections) * 2; // *2 for reverse
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $attempts++;
            
            // Random starting position
            $startR = mt_rand(0, $gridSize - 1);
            $startC = mt_rand(0, $gridSize - 1);
            
            // Random direction
            $direction = $availableDirections[array_rand($availableDirections)];
            
            // Try both normal and reverse
            $wordToPlace = $reverse && mt_rand(0, 1) ? strrev($word) : $word;
            
            if ($this->canPlaceWord($grid, $wordToPlace, $startR, $startC, $direction)) {
                $this->placeWordInGrid($grid, $wordToPlace, $startR, $startC, $direction);
                
                return [
                    'word' => $word,
                    'placed_word' => $wordToPlace,
                    'start' => [$startR, $startC],
                    'end' => [
                        $startR + ($direction[0] * ($wordLen - 1)),
                        $startC + ($direction[1] * ($wordLen - 1))
                    ],
                    'direction' => $direction,
                    'reversed' => $wordToPlace !== $word
                ];
            }
        }

        return null;
    }

    private function getAvailableDirections(bool $diagonals): array
    {
        $directions = [$this->directions['horizontal'], $this->directions['vertical']];
        
        if ($diagonals) {
            $directions[] = $this->directions['diagonal_down'];
            $directions[] = $this->directions['diagonal_up'];
        }
        
        return $directions;
    }

    private function canPlaceWord(array $grid, string $word, int $startR, int $startC, array $direction): bool
    {
        $wordLen = strlen($word);
        $gridSize = count($grid);

        // Check if word fits within grid bounds
        $endR = $startR + ($direction[0] * ($wordLen - 1));
        $endC = $startC + ($direction[1] * ($wordLen - 1));

        if ($endR < 0 || $endR >= $gridSize || $endC < 0 || $endC >= $gridSize) {
            return false;
        }

        // Check if cells are available
        for ($i = 0; $i < $wordLen; $i++) {
            $r = $startR + ($direction[0] * $i);
            $c = $startC + ($direction[1] * $i);
            
            $currentCell = $grid[$r][$c];
            $wordChar = $word[$i];
            
            // Cell must be empty or contain the same letter
            if ($currentCell !== '' && $currentCell !== $wordChar) {
                return false;
            }
        }

        return true;
    }

    private function placeWordInGrid(array &$grid, string $word, int $startR, int $startC, array $direction): void
    {
        $wordLen = strlen($word);
        
        for ($i = 0; $i < $wordLen; $i++) {
            $r = $startR + ($direction[0] * $i);
            $c = $startC + ($direction[1] * $i);
            $grid[$r][$c] = $word[$i];
        }
    }

    private function fillEmptyCells(array &$grid): void
    {
        $gridSize = count($grid);
        
        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if ($grid[$r][$c] === '') {
                    $grid[$r][$c] = $this->alphabet[mt_rand(0, strlen($this->alphabet) - 1)];
                }
            }
        }
    }

    private function generatePuzzleId(): string
    {
        return 'puzzle_' . uniqid() . '_' . mt_rand(1000, 9999);
    }

    public function validateWordSelection(array $grid, array $selection, array $placedWords): bool
    {
        if (empty($selection) || count($selection) < 2) {
            return false;
        }

        // Sort selection by row and column
        usort($selection, function($a, $b) {
            if ($a['r'] !== $b['r']) {
                return $a['r'] - $b['r'];
            }
            return $a['c'] - $b['c'];
        });

        // Check if selection forms a straight line
        if (!$this->isStraightLine($selection)) {
            return false;
        }

        // Extract word from selection
        $word = '';
        foreach ($selection as $cell) {
            $word .= $grid[$cell['r']][$cell['c']];
        }

        // Check if word exists in placed words
        foreach ($placedWords as $placedWord) {
            if ($placedWord['placed_word'] === $word || $placedWord['placed_word'] === strrev($word)) {
                return true;
            }
        }

        return false;
    }

    private function isStraightLine(array $selection): bool
    {
        if (count($selection) < 2) {
            return false;
        }

        $first = $selection[0];
        $last = $selection[count($selection) - 1];
        
        $deltaR = $last['r'] - $first['r'];
        $deltaC = $last['c'] - $first['c'];
        
        // Check if it's horizontal, vertical, or diagonal
        if ($deltaR === 0) {
            // Horizontal
            return $this->checkHorizontalLine($selection);
        } elseif ($deltaC === 0) {
            // Vertical
            return $this->checkVerticalLine($selection);
        } elseif (abs($deltaR) === abs($deltaC)) {
            // Diagonal
            return $this->checkDiagonalLine($selection);
        }
        
        return false;
    }

    private function checkHorizontalLine(array $selection): bool
    {
        $row = $selection[0]['r'];
        $cols = array_column($selection, 'c');
        sort($cols);
        
        for ($i = 1; $i < count($cols); $i++) {
            if ($cols[$i] !== $cols[$i-1] + 1) {
                return false;
            }
        }
        
        return true;
    }

    private function checkVerticalLine(array $selection): bool
    {
        $col = $selection[0]['c'];
        $rows = array_column($selection, 'r');
        sort($rows);
        
        for ($i = 1; $i < count($rows); $i++) {
            if ($rows[$i] !== $rows[$i-1] + 1) {
                return false;
            }
        }
        
        return true;
    }

    private function checkDiagonalLine(array $selection): bool
    {
        $first = $selection[0];
        $last = $selection[count($selection) - 1];
        
        $deltaR = $last['r'] - $first['r'];
        $deltaC = $last['c'] - $first['c'];
        
        $stepR = $deltaR > 0 ? 1 : -1;
        $stepC = $deltaC > 0 ? 1 : -1;
        
        $currentR = $first['r'];
        $currentC = $first['c'];
        
        foreach ($selection as $cell) {
            if ($cell['r'] !== $currentR || $cell['c'] !== $currentC) {
                return false;
            }
            $currentR += $stepR;
            $currentC += $stepC;
        }
        
        return true;
    }

    /**
     * Find a replacement word that hasn't been used yet
     */
    private function findReplacementWord(string $failedWord, array $allWords, array $usedWords, int $gridSize): ?string
    {
        // Filter out words that are too long for the grid
        $candidates = array_filter($allWords, function($word) use ($gridSize) {
            $word = strtoupper(trim($word));
            return strlen($word) <= $gridSize && 
                   strlen($word) >= 3 && 
                   preg_match('/^[A-Z]+$/', $word);
        });

        // Remove already used words
        $candidates = array_diff($candidates, $usedWords);
        
        // Remove the failed word itself
        $candidates = array_diff($candidates, [$failedWord]);
        
        // If no candidates, return null
        if (empty($candidates)) {
            return null;
        }
        
        // Return a random candidate
        return $candidates[array_rand($candidates)];
    }
}
