<?php

declare(strict_types=1);

namespace App\Services;

class PuzzleGenerator
{
    private array $config;
    private array $grid;
    private array $placedWords = [];
    private int $size;
    private array $directions;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->directions = $config['game']['directions'];
    }

    public function generate(array $words, array $options): array
    {
        $this->size = $options['size'] ?? 12;
        $this->grid = array_fill(0, $this->size, array_fill(0, $this->size, ''));
        $this->placedWords = [];

        // Sort words by length (longest first) for better placement
        usort($words, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        // Filter words by max length
        $words = array_filter($words, function($word) {
            return strlen($word) <= $this->config['game']['maxWordLen'];
        });

        // Set seed for reproducible puzzles
        if (isset($options['seed'])) {
            mt_srand($options['seed']);
        }

        // Place words with retry logic
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            $allWordsPlaced = true;
            $this->placedWords = [];
            $this->grid = array_fill(0, $this->size, array_fill(0, $this->size, ''));
            
            foreach ($words as $word) {
                if (!$this->placeWord($word, $options)) {
                    $allWordsPlaced = false;
                    break;
                }
            }
            
            if ($allWordsPlaced) {
                break;
            }
            
            $retryCount++;
            // Increase grid size slightly if words can't fit
            if ($retryCount === 1) {
                $this->size = min($this->size + 2, 20);
                $this->grid = array_fill(0, $this->size, array_fill(0, $this->size, ''));
            }
        }

        // Fill empty cells with random letters
        $this->fillEmptyCells();

        return [
            'grid' => $this->grid,
            'words' => $words,
            'placed' => $this->placedWords,
            'size' => $this->size,
            'seed' => $options['seed'] ?? mt_rand(),
        ];
    }

    private function placeWord(string $word, array $options): bool
    {
        $maxAttempts = 200; // Increased attempts
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $direction = $this->getRandomDirection($options);
            $reverse = $this->shouldReverse($options);
            
            $wordToPlace = $reverse ? strrev($word) : $word;
            
            $placement = $this->tryPlaceWord($wordToPlace, $direction);
            if ($placement) {
                $this->placedWords[] = [
                    'word' => $word,
                    'placed' => $wordToPlace,
                    'direction' => $direction,
                    'reverse' => $reverse,
                    'startRow' => $placement['startRow'],
                    'startCol' => $placement['startCol'],
                ];
                return true;
            }
            
            $attempts++;
        }

        return false;
    }

    private function getRandomDirection(array $options): array
    {
        $availableDirections = ['horizontal', 'vertical'];
        
        if (($options['diagonals'] ?? false)) {
            $availableDirections[] = 'diagonal_down';
            $availableDirections[] = 'diagonal_up';
        }
        
        $directionName = $availableDirections[array_rand($availableDirections)];
        return $this->directions[$directionName];
    }

    private function shouldReverse(array $options): bool
    {
        if (!($options['reverse'] ?? false)) {
            return false;
        }
        
        return (bool) mt_rand(0, 1);
    }

    private function tryPlaceWord(string $word, array $direction): array|false
    {
        $wordLen = strlen($word);
        
        // Create a scoring system to prefer positions that spread words out
        $bestPosition = null;
        $bestScore = -1;
        
        // Try multiple random positions and score them
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $startRow = mt_rand(0, $this->size - 1);
            $startCol = mt_rand(0, $this->size - 1);
            
            if ($this->canPlaceWordAt($word, $startRow, $startCol, $direction)) {
                $score = $this->calculatePlacementScore($word, $startRow, $startCol, $direction);
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestPosition = ['startRow' => $startRow, 'startCol' => $startCol];
                }
            }
        }
        
        // If we found a good position, use it
        if ($bestPosition && $bestScore > 0) {
            $this->placeWordAt($word, $bestPosition['startRow'], $bestPosition['startCol'], $direction);
            return $bestPosition;
        }
        
        // Fallback: try systematic placement
        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if ($this->canPlaceWordAt($word, $row, $col, $direction)) {
                    $this->placeWordAt($word, $row, $col, $direction);
                    return ['startRow' => $row, 'startCol' => $col];
                }
            }
        }
        
        return false;
    }

    private function canPlaceWordAt(string $word, int $startRow, int $startCol, array $direction): bool
    {
        $wordLen = strlen($word);
        $dr = $direction[0];
        $dc = $direction[1];
        
        // Check if word fits within bounds
        $endRow = $startRow + ($dr * ($wordLen - 1));
        $endCol = $startCol + ($dc * ($wordLen - 1));
        
        if ($endRow < 0 || $endRow >= $this->size || $endCol < 0 || $endCol >= $this->size) {
            return false;
        }
        
        // Check if cells are available or can overlap
        for ($i = 0; $i < $wordLen; $i++) {
            $row = $startRow + ($dr * $i);
            $col = $startCol + ($dc * $i);
            $cell = $this->grid[$row][$col];
            $letter = $word[$i];
            
            if ($cell !== '' && $cell !== $letter) {
                return false;
            }
        }
        
        return true;
    }

    private function placeWordAt(string $word, int $startRow, int $startCol, array $direction): void
    {
        $wordLen = strlen($word);
        $dr = $direction[0];
        $dc = $direction[1];
        
        for ($i = 0; $i < $wordLen; $i++) {
            $row = $startRow + ($dr * $i);
            $col = $startCol + ($dc * $i);
            $this->grid[$row][$col] = $word[$i];
        }
    }

    private function fillEmptyCells(): void
    {
        $alphabet = $this->config['game']['alphabet'];
        
        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if ($this->grid[$row][$col] === '') {
                    $this->grid[$row][$col] = $alphabet[array_rand($alphabet)];
                }
            }
        }
    }
    
    private function calculatePlacementScore(string $word, int $startRow, int $startCol, array $direction): int
    {
        $score = 0;
        $wordLen = strlen($word);
        $dr = $direction[0];
        $dc = $direction[1];
        
        // Prefer positions away from the center (spread to edges)
        $center = (int)($this->size / 2);
        $distanceFromCenter = abs($startRow - $center) + abs($startCol - $center);
        $score += $distanceFromCenter * 2;
        
        // Prefer positions that don't overlap with existing words
        $overlapPenalty = 0;
        for ($i = 0; $i < $wordLen; $i++) {
            $row = $startRow + ($dr * $i);
            $col = $startCol + ($dc * $i);
            
            if ($this->grid[$row][$col] !== '') {
                $overlapPenalty += 5; // Heavy penalty for overlapping
            }
        }
        $score -= $overlapPenalty;
        
        // Prefer positions that spread words across different quadrants
        $quadrant = $this->getQuadrant($startRow, $startCol);
        $quadrantCount = $this->countWordsInQuadrant($quadrant);
        $score += (4 - $quadrantCount) * 3; // Prefer less crowded quadrants
        
        // Prefer positions that use the full board dimensions
        $score += min($startRow, $this->size - 1 - $startRow) + min($startCol, $this->size - 1 - $startCol);
        
        return $score;
    }
    
    private function getQuadrant(int $row, int $col): int
    {
        $midRow = (int)($this->size / 2);
        $midCol = (int)($this->size / 2);
        
        if ($row < $midRow) {
            return $col < $midCol ? 0 : 1; // Top-left or Top-right
        } else {
            return $col < $midCol ? 2 : 3; // Bottom-left or Bottom-right
        }
    }
    
    private function countWordsInQuadrant(int $quadrant): int
    {
        $count = 0;
        foreach ($this->placedWords as $placedWord) {
            $wordQuadrant = $this->getQuadrant($placedWord['startRow'], $placedWord['startCol']);
            if ($wordQuadrant === $quadrant) {
                $count++;
            }
        }
        return $count;
    }
}
