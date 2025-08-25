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

        error_log("Generating puzzle - Grid size: {$size}, Target words: {$targetWordCount}, Total words available: " . count($words));

        if ($seed !== null) {
            mt_srand($seed);
        }

        // Get ALL words, randomize them, then process one by one
        $this->logToFile("Total words in theme: " . count($words));
        
        // Randomize the entire word list first using PHP's shuffle()
        $randomizedWords = $words;
        shuffle($randomizedWords);
        $this->logToFile("Randomized all words: " . count($randomizedWords));
        
        // Show first 20 randomized words for debugging
        $firstWords = array_slice($randomizedWords, 0, 20);
        $this->logToFile("First 20 randomized words: " . implode(', ', $firstWords));
        
        // Show word lengths to verify randomization
        $wordLengths = array_map(function($word) { return strlen($word); }, $firstWords);
        $this->logToFile("First 20 word lengths: " . implode(', ', $wordLengths));
        
        // Create a pool of available words for placement
        $wordPool = $randomizedWords;
        $usedWords = [];

        // Generate grid
        $grid = $this->createEmptyGrid($size);
        $placedWords = [];
        $failedWords = [];
        $attempts = 0;
        $maxAttempts = count($wordPool) * 3; // Allow more attempts for better placement

        // Try to place words until we reach target count or run out of attempts/words
        while (count($placedWords) < $targetWordCount && $attempts < $maxAttempts && !empty($wordPool)) {
            $attempts++;
            
            // Get next word from pool
            $word = array_shift($wordPool);
            $wordLength = strlen(strtoupper(trim($word)));
            $maxAllowedLength = (int)($size * 0.6); // 60% of grid size
            
            $this->logToFile("Processing word '{$word}' (length: {$wordLength}, max allowed: {$maxAllowedLength})");
            
            // Check if word meets length requirements
            if ($wordLength > $maxAllowedLength) {
                $this->logToFile("Word '{$word}' too long ({$wordLength} > {$maxAllowedLength}), skipping");
                continue; // Skip this word and try the next one
            }
            
            if ($wordLength < 3) {
                $this->logToFile("Word '{$word}' too short ({$wordLength} < 3), skipping");
                continue; // Skip this word and try the next one
            }
            
            // Check if word contains valid characters
            if (!preg_match('/^[A-Z ]+$/', strtoupper(trim($word)))) {
                $this->logToFile("Word '{$word}' contains invalid characters, skipping");
                continue; // Skip this word and try the next one
            }
            
            $this->logToFile("Attempting to place word '{$word}' (attempt {$attempts})");
            
            $placed = $this->placeWord($grid, $word, $diagonals, $reverse);
            
            if ($placed) {
                $placedWords[] = $placed;
                $usedWords[] = $word;
                $this->logToFile("Successfully placed word '{$word}' - total placed: " . count($placedWords));
                
                // Log grid state after placement
                $emptyCells = 0;
                for ($r = 0; $r < $size; $r++) {
                    for ($c = 0; $c < $size; $c++) {
                        if ($grid[$r][$c] === '') $emptyCells++;
                    }
                }
                $this->logToFile("Grid state: {$emptyCells} empty cells remaining");
            } else {
                $failedWords[] = $word;
                $this->logToFile("Failed to place word '{$word}' - total failed: " . count($failedWords));
                
                // If we still need more words, try to get a replacement word from the original list
                if (count($placedWords) < $targetWordCount) {
                    $replacementWord = $this->findReplacementWord($word, $words, $usedWords, $size);
                    if ($replacementWord) {
                        $wordPool[] = $replacementWord;
                        $this->logToFile("Added replacement word '{$replacementWord}' to pool");
                    }
                }
            }
        }

        // Note: We're now processing all words in the main loop above, so no need for additional word placement here
        
        // Log placement results
        $this->logToFile("Word placement complete - Placed: " . count($placedWords) . ", Failed: " . count($failedWords) . ", Target: {$targetWordCount}");

        // Fill empty cells with random letters
        $this->fillEmptyCells($grid);

        // Generate unique puzzle ID
        $puzzleId = $this->generatePuzzleId();

        $finalWords = array_slice($usedWords, 0, $targetWordCount);
        $finalPlacedWords = array_slice($placedWords, 0, $targetWordCount);
        
        $this->logToFile("Final result - Target: {$targetWordCount}, Actual words: " . count($finalWords) . ", Placed words: " . count($finalPlacedWords));
        $this->logToFile("Words returned: " . implode(', ', $finalWords));

        return [
            'id' => $puzzleId,
            'grid' => $grid,
            'words' => $finalWords,
            'placed_words' => $finalPlacedWords,
            'failed_words' => $failedWords,
            'size' => $size,
            'options' => $options,
            'seed' => $seed
        ];
    }

    private function filterWords(array $words, int $gridSize): array
    {
        $maxAllowedLength = (int)($gridSize * 0.6); // 60% of grid width
        $filteredWords = array_filter($words, function($word) use ($gridSize, $maxAllowedLength) {
            $word = strtoupper(trim($word));
            $wordLength = strlen($word);
            
            // Log words that are filtered out for debugging
            if ($wordLength > $maxAllowedLength) {
                $this->logToFile("Word '{$word}' (length: {$wordLength}) filtered out - exceeds {$maxAllowedLength} (60% of grid size {$gridSize})");
            } elseif ($wordLength < 3) {
                $this->logToFile("Word '{$word}' (length: {$wordLength}) filtered out - too short (minimum 3)");
            } elseif (!preg_match('/^[A-Z ]+$/', $word)) {
                $this->logToFile("Word '{$word}' filtered out - contains invalid characters");
            }
            
            return $wordLength <= $maxAllowedLength && 
                   $wordLength >= 3 && 
                   preg_match('/^[A-Z ]+$/', $word);
        });
        
        $this->logToFile("Filtered words: " . count($filteredWords) . " out of " . count($words) . " (grid size: {$gridSize}, max length: {$maxAllowedLength})");
        
        return $filteredWords;
    }

    private function sortWordsByLength(array $words): array
    {
        usort($words, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        return $words;
    }

    /**
     * Get random words from the list using Fisher-Yates shuffle
     */
    private function getRandomWords(array $words, int $count): array
    {
        if (count($words) <= $count) {
            return $words;
        }
        
        // Fisher-Yates shuffle for better performance and true randomness
        $shuffled = $words;
        $total = count($shuffled);
        
        for ($i = $total - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            // Swap elements
            $temp = $shuffled[$i];
            $shuffled[$i] = $shuffled[$j];
            $shuffled[$j] = $temp;
        }
        
        return array_slice($shuffled, 0, $count);
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

        $this->logToFile("=== PLACING WORD: '{$word}' (length: {$wordLen}) ===");

        // Get available directions
        $availableDirections = $this->getAvailableDirections($diagonals);
        $this->logToFile("Available directions: " . count($availableDirections) . " (diagonals: " . ($diagonals ? 'true' : 'false') . ")");
        
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
            
            if ($attempts % 1000 === 0) {
                $this->logToFile("  Attempt {$attempts}: pos({$startR},{$startC}), dir[" . implode(',', $direction) . "], word: '{$wordToPlace}'");
            }
            
            if ($this->canPlaceWord($grid, $wordToPlace, $startR, $startC, $direction)) {
                $this->logToFile("  SUCCESS at attempt {$attempts}: pos({$startR},{$startC}), dir[" . implode(',', $direction) . "], word: '{$wordToPlace}'");
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

        $this->logToFile("  FAILED to place word '{$word}' after {$maxAttempts} attempts");
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
        
        // Create alphabet without spaces for random filling
        $fillAlphabet = str_replace(' ', '', $this->alphabet);
        
        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if ($grid[$r][$c] === '') {
                    $grid[$r][$c] = $fillAlphabet[mt_rand(0, strlen($fillAlphabet) - 1)];
                }
            }
        }
    }

    private function generatePuzzleId(): string
    {
        return 'p_' . uniqid() . mt_rand(100, 999);
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

    /**
     * Log messages to the generator log file
     */
    private function logToFile(string $message): void
    {
        $logFile = '/var/www/html/WordSearch/Dev/log/generator.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Ensure the log directory exists and is writable
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to log file
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

