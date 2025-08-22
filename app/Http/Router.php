<?php

declare(strict_types=1);

namespace App\Http;

use App\Services\DatabaseService;
use App\Services\PuzzleGenerator;
use App\Services\PuzzleStore;
use App\Services\ThemeService;

class Router
{
    private array $routes = [];
    private array $config;
    private DatabaseService $dbService;

    public function __construct(array $config, DatabaseService $dbService)
    {
        $this->config = $config;
        $this->dbService = $dbService;
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Check for exact match first
        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            $handler();
            return;
        }
        
        // Check for parameterized routes
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->routeToPattern($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $handler(...$matches);
                return;
            }
        }
        
        // No route found
        http_response_code(404);
        echo '404 Not Found';
    }

    private function routeToPattern(string $route): string
    {
        return '#^' . preg_replace('#\{([^}]+)\}#', '([^/]+)', $route) . '$#';
    }

    public function handleGeneratePuzzle(): string
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['words']) || !isset($input['options'])) {
                throw new \Exception('Invalid input data');
            }

            $words = $input['words'];
            $options = $input['options'];

            // Validate input
            if (empty($words) || !is_array($words)) {
                throw new \Exception('Words array is required');
            }

            if (!isset($options['size']) || !isset($options['diagonals']) || !isset($options['reverse'])) {
                throw new \Exception('Invalid options');
            }

            // Generate puzzle
            $generator = new PuzzleGenerator($this->config);
            $puzzle = $generator->generate($words, $options);

            // Generate unique ID for the puzzle
            $puzzleId = uniqid('puzzle_', true);
            $puzzle['id'] = $puzzleId;

            // Store puzzle in database
            $this->storePuzzleInDatabase($puzzle, $options);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'id' => $puzzleId,
                'message' => 'Puzzle generated successfully'
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleGetPuzzle(string $id): string
    {
        try {
            $puzzle = $this->getPuzzleFromDatabase($id);
            
            if (!$puzzle) {
                throw new \Exception('Puzzle not found');
            }

            header('Content-Type: application/json');
            return json_encode($puzzle);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(404);
            return json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleValidateWord(): string
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['puzzleId']) || !isset($input['word'])) {
                throw new \Exception('Invalid input data');
            }

            $puzzleId = $input['puzzleId'];
            $word = $input['word'];

            $puzzle = $this->getPuzzleFromDatabase($puzzleId);
            if (!$puzzle) {
                throw new \Exception('Puzzle not found');
            }

            $isValid = in_array($word, $puzzle['words']);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'valid' => $isValid,
                'word' => $word
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleGetThemes(): string
    {
        try {
            $themeService = new ThemeService($this->config);
            $themes = $themeService->getAvailableThemes();

            header('Content-Type: application/json');
            return json_encode($themes);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            return json_encode([
                'error' => 'Failed to load themes'
            ]);
        }
    }

    private function storePuzzleInDatabase(array $puzzle, array $options): string
    {
        try {
            $puzzleId = $puzzle['id'];
            
            $this->dbService->insert('puzzles', [
                'puzzle_id' => $puzzleId,
                'theme' => 'custom', // Default theme for generated puzzles
                'difficulty' => $this->getDifficultyFromSize($options['size']),
                'grid_size' => $options['size'],
                'words' => json_encode($puzzle['words']),
                'grid' => json_encode($puzzle['grid']),
                'placed_words' => json_encode($puzzle['placed']),
                'seed' => $puzzle['seed']
            ]);

            return $puzzleId;

        } catch (\Exception $e) {
            // Fallback to file storage if database fails
            $store = new PuzzleStore();
            $store->save($puzzle);
            return $puzzle['id'];
        }
    }

    private function getPuzzleFromDatabase(string $id): ?array
    {
        try {
            $puzzle = $this->dbService->findOne('puzzles', 'puzzle_id = :id', ['id' => $id]);
            
            if (!$puzzle) {
                return null;
            }

            return [
                'id' => $puzzle['puzzle_id'],
                'grid' => json_decode($puzzle['grid'], true),
                'words' => json_decode($puzzle['words'], true),
                'placed' => json_decode($puzzle['placed_words'], true),
                'size' => $puzzle['grid_size'],
                'seed' => $puzzle['seed']
            ];

        } catch (\Exception $e) {
            // Fallback to file storage if database fails
            $store = new PuzzleStore();
            return $store->load($id);
        }
    }

    private function getDifficultyFromSize(int $size): string
    {
        if ($size <= 10) return 'easy';
        if ($size <= 15) return 'medium';
        return 'hard';
    }
}
