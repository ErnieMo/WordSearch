<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    private array $routes = [];
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        // Remove query string
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        // Check if route exists
        if (!isset($this->routes[$method][$path])) {
            // Check for API routes
            if (str_starts_with($path, '/api/')) {
                return $this->handleApiRoute($method, $path);
            }
            
            // Default to home page
            if ($path !== '/') {
                $path = '/';
            }
        }

        $handler = $this->routes[$method][$path] ?? $this->routes['GET']['/'];
        
        return $handler($this->config);
    }

    private function handleApiRoute(string $method, string $path): mixed
    {
        // Extract API endpoint
        $endpoint = substr($path, 5); // Remove '/api/'
        
        switch ($endpoint) {
            case 'generate':
                if ($method === 'POST') {
                    return $this->handleGeneratePuzzle();
                }
                break;
            case (preg_match('/^puzzle\/(.+)$/', $endpoint, $matches) ? true : false):
                if ($method === 'GET') {
                    return $this->handleGetPuzzle($matches[1]);
                }
                break;
            case 'validate':
                if ($method === 'POST') {
                    return $this->handleValidateWord();
                }
                break;
        }

        http_response_code(404);
        return json_encode(['error' => 'API endpoint not found']);
    }

    private function handleGeneratePuzzle(): string
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['words']) || !isset($input['options'])) {
            http_response_code(400);
            return json_encode(['error' => 'Invalid input']);
        }

        $generator = new \App\Services\PuzzleGenerator($this->config);
        $puzzle = $generator->generate($input['words'], $input['options']);
        
        $store = new \App\Services\PuzzleStore($this->config);
        $id = $store->save($puzzle);
        
        $puzzle['id'] = $id;
        
        header('Content-Type: application/json');
        return json_encode($puzzle);
    }

    private function handleGetPuzzle(string $id): string
    {
        $store = new \App\Services\PuzzleStore($this->config);
        $puzzle = $store->load($id);
        
        if (!$puzzle) {
            http_response_code(404);
            return json_encode(['error' => 'Puzzle not found']);
        }
        
        header('Content-Type: application/json');
        return json_encode($puzzle);
    }

    private function handleValidateWord(): string
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id']) || !isset($input['selection'])) {
            http_response_code(400);
            return json_encode(['error' => 'Invalid input']);
        }

        // For MVP, we'll do basic validation
        // In a full implementation, this would validate against the stored puzzle
        $store = new \App\Services\PuzzleStore($this->config);
        $puzzle = $store->load($input['id']);
        
        if (!$puzzle) {
            http_response_code(404);
            return json_encode(['error' => 'Puzzle not found']);
        }

        // Simple validation - check if selection forms a valid word
        $isValid = $this->validateSelection($input['selection'], $puzzle);
        
        header('Content-Type: application/json');
        return json_encode(['valid' => $isValid]);
    }

    private function validateSelection(array $selection, array $puzzle): bool
    {
        // Basic validation - check if selection coordinates are within bounds
        $size = $puzzle['size'];
        
        foreach ($selection as $coord) {
            if (!isset($coord['r0'], $coord['c0'], $coord['r1'], $coord['c1'])) {
                return false;
            }
            
            if ($coord['r0'] < 0 || $coord['r0'] >= $size || 
                $coord['c0'] < 0 || $coord['c0'] >= $size ||
                $coord['r1'] < 0 || $coord['r1'] >= $size || 
                $coord['c1'] < 0 || $coord['c1'] >= $size) {
                return false;
            }
        }
        
        return true;
    }
}
