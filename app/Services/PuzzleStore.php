<?php

declare(strict_types=1);

namespace App\Services;

class PuzzleStore
{
    private string $storagePath;
    private string $puzzlesPath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../storage/';
        $this->puzzlesPath = $this->storagePath . 'puzzles/';
        
        // Ensure directories exist
        $this->ensureDirectoriesExist();
    }

    private function ensureDirectoriesExist(): void
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }
        
        if (!is_dir($this->puzzlesPath)) {
            mkdir($this->puzzlesPath, 0775, true);
        }
    }

    public function savePuzzle(array $puzzleData): string
    {
        $puzzleId = $puzzleData['id'] ?? $this->generatePuzzleId();
        $filename = $this->puzzlesPath . $puzzleId . '.json';
        
        $puzzleToSave = [
            'id' => $puzzleId,
            'grid' => $puzzleData['grid'],
            'words' => $puzzleData['words'],
            'placed_words' => $puzzleData['placed_words'] ?? [],
            'failed_words' => $puzzleData['failed_words'] ?? [],
            'size' => $puzzleData['size'],
            'options' => $puzzleData['options'] ?? [],
            'seed' => $puzzleData['seed'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'theme' => $puzzleData['theme'] ?? 'unknown',
            'difficulty' => $puzzleData['difficulty'] ?? 'medium'
        ];
        
        $jsonData = json_encode($puzzleToSave, JSON_PRETTY_PRINT);
        
        if (file_put_contents($filename, $jsonData) === false) {
            throw new \RuntimeException("Failed to save puzzle to file: {$filename}");
        }
        
        return $puzzleId;
    }

    public function getPuzzle(string $puzzleId): ?array
    {
        $filename = $this->puzzlesPath . $puzzleId . '.json';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $jsonData = file_get_contents($filename);
        if ($jsonData === false) {
            return null;
        }
        
        $puzzle = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $puzzle;
    }

    public function deletePuzzle(string $puzzleId): bool
    {
        $filename = $this->puzzlesPath . $puzzleId . '.json';
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    public function listPuzzles(int $limit = 100): array
    {
        $puzzles = [];
        $files = glob($this->puzzlesPath . '*.json');
        
        // Sort by creation time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $count = 0;
        foreach ($files as $file) {
            if ($count >= $limit) {
                break;
            }
            
            $puzzleId = basename($file, '.json');
            $puzzle = $this->getPuzzle($puzzleId);
            
            if ($puzzle) {
                $puzzles[] = [
                    'id' => $puzzleId,
                    'theme' => $puzzle['theme'] ?? 'unknown',
                    'difficulty' => $puzzle['difficulty'] ?? 'medium',
                    'size' => $puzzle['size'],
                    'word_count' => count($puzzle['words']),
                    'created_at' => $puzzle['created_at'] ?? 'unknown'
                ];
                $count++;
            }
        }
        
        return $puzzles;
    }

    public function searchPuzzles(array $criteria): array
    {
        $puzzles = [];
        $files = glob($this->puzzlesPath . '*.json');
        
        foreach ($files as $file) {
            $puzzleId = basename($file, '.json');
            $puzzle = $this->getPuzzle($puzzleId);
            
            if ($puzzle && $this->matchesCriteria($puzzle, $criteria)) {
                $puzzles[] = $puzzle;
            }
        }
        
        return $puzzles;
    }

    private function matchesCriteria(array $puzzle, array $criteria): bool
    {
        foreach ($criteria as $key => $value) {
            if (!isset($puzzle[$key])) {
                return false;
            }
            
            if (is_array($value)) {
                if (!in_array($puzzle[$key], $value)) {
                    return false;
                }
            } else {
                if ($puzzle[$key] !== $value) {
                    return false;
                }
            }
        }
        
        return true;
    }

    public function getPuzzleStats(): array
    {
        $files = glob($this->puzzlesPath . '*.json');
        $totalPuzzles = count($files);
        
        $themes = [];
        $difficulties = [];
        $sizes = [];
        
        foreach ($files as $file) {
            $puzzleId = basename($file, '.json');
            $puzzle = $this->getPuzzle($puzzleId);
            
            if ($puzzle) {
                $theme = $puzzle['theme'] ?? 'unknown';
                $difficulty = $puzzle['difficulty'] ?? 'medium';
                $size = $puzzle['size'] ?? 15;
                
                $themes[$theme] = ($themes[$theme] ?? 0) + 1;
                $difficulties[$difficulty] = ($difficulties[$difficulty] ?? 0) + 1;
                $sizes[$size] = ($sizes[$size] ?? 0) + 1;
            }
        }
        
        return [
            'total_puzzles' => $totalPuzzles,
            'themes' => $themes,
            'difficulties' => $difficulties,
            'sizes' => $sizes
        ];
    }

    public function cleanupOldPuzzles(int $daysOld = 30): int
    {
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        $deletedCount = 0;
        
        $files = glob($this->puzzlesPath . '*.json');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    private function generatePuzzleId(): string
    {
        return 'file_puzzle_' . uniqid() . '_' . mt_rand(1000, 9999);
    }

    public function isAvailable(): bool
    {
        return is_dir($this->puzzlesPath) && is_writable($this->puzzlesPath);
    }

    public function getStorageInfo(): array
    {
        $totalSize = 0;
        $fileCount = 0;
        
        $files = glob($this->puzzlesPath . '*.json');
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $fileCount++;
        }
        
        return [
            'path' => $this->puzzlesPath,
            'file_count' => $fileCount,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'writable' => is_writable($this->puzzlesPath)
        ];
    }
}
