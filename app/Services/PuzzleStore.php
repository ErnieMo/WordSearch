<?php

declare(strict_types=1);

namespace App\Services;

class PuzzleStore
{
    private array $config;
    private string $storagePath;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storagePath = $config['paths']['storage'] . '/puzzles';
        
        // Ensure storage directory exists
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }
    }

    public function save(array $puzzle): string
    {
        $id = $this->generateId();
        $filename = $this->storagePath . '/' . $id . '.json';
        
        $puzzleData = [
            'id' => $id,
            'created_at' => date('Y-m-d H:i:s'),
            'grid' => $puzzle['grid'],
            'words' => $puzzle['words'],
            'placed' => $puzzle['placed'],
            'size' => $puzzle['size'],
            'seed' => $puzzle['seed'],
        ];
        
        if (file_put_contents($filename, json_encode($puzzleData, JSON_PRETTY_PRINT))) {
            return $id;
        }
        
        throw new \RuntimeException('Failed to save puzzle');
    }

    public function load(string $id): ?array
    {
        $filename = $this->storagePath . '/' . $id . '.json';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return null;
        }
        
        $puzzle = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $puzzle;
    }

    public function delete(string $id): bool
    {
        $filename = $this->storagePath . '/' . $id . '.json';
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return false;
    }

    public function list(): array
    {
        $puzzles = [];
        $files = glob($this->storagePath . '/*.json');
        
        foreach ($files as $file) {
            $id = basename($file, '.json');
            $puzzle = $this->load($id);
            if ($puzzle) {
                $puzzles[] = [
                    'id' => $id,
                    'created_at' => $puzzle['created_at'],
                    'size' => $puzzle['size'],
                    'word_count' => count($puzzle['words']),
                ];
            }
        }
        
        // Sort by creation date (newest first)
        usort($puzzles, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $puzzles;
    }

    private function generateId(): string
    {
        // Generate a short, unique ID
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $id = base_convert($timestamp . $random, 10, 36);
        
        // Ensure it's unique
        $counter = 0;
        $originalId = $id;
        while (file_exists($this->storagePath . '/' . $id . '.json')) {
            $counter++;
            $id = $originalId . base_convert($counter, 10, 36);
        }
        
        return $id;
    }
}
