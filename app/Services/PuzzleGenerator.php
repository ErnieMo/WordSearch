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

        // Place words
        foreach ($words as $word) {
            $this->placeWord($word, $options);
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
        $maxAttempts = 100;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $direction = $this->getRandomDirection($options);
            $reverse = $this->shouldReverse($options);
            
            $wordToPlace = $reverse ? strrev($word) : $word;
            
            if ($this->tryPlaceWord($wordToPlace, $direction)) {
                $this->placedWords[] = [
                    'word' => $word,
                    'placed' => $wordToPlace,
                    'direction' => $direction,
                    'reverse' => $reverse,
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

    private function tryPlaceWord(string $word, array $direction): bool
    {
        $wordLen = strlen($word);
        
        // Try multiple starting positions
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $startRow = mt_rand(0, $this->size - 1);
            $startCol = mt_rand(0, $this->size - 1);
            
            if ($this->canPlaceWordAt($word, $startRow, $startCol, $direction)) {
                $this->placeWordAt($word, $startRow, $startCol, $direction);
                return true;
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
}
