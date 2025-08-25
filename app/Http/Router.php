<?php

declare(strict_types=1);

namespace App\Http;

use App\Services\AuthService;
use App\Services\PuzzleGenerator;
use App\Services\ThemeService;
use App\Services\GameService;
use App\Services\DatabaseService;

class Router
{
    private array $routes = [];
    private AuthService $authService;
    private PuzzleGenerator $puzzleGenerator;
    private ThemeService $themeService;
    private GameService $gameService;
    private DatabaseService $dbService;

    public function __construct()
    {
        $this->dbService = new DatabaseService();
        $this->authService = new AuthService($this->dbService);
        $this->puzzleGenerator = new PuzzleGenerator();
        $this->themeService = new ThemeService();
        $this->gameService = new GameService($this->dbService);
        
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // Authentication routes
        $this->addRoute('POST', '/api/auth/register', [$this, 'handleRegister']);
        $this->addRoute('POST', '/api/auth/login', [$this, 'handleLogin']);
        $this->addRoute('POST', '/api/auth/logout', [$this, 'handleLogout']);
        $this->addRoute('GET', '/api/auth/profile', [$this, 'handleGetProfile']);
        $this->addRoute('POST', '/api/auth/profile/update', [$this, 'handleUpdateProfile']);
        $this->addRoute('POST', '/api/auth/password/change', [$this, 'handleChangePassword']);

        // Game routes
        $this->addRoute('POST', '/api/generate', [$this, 'handleGeneratePuzzle']);
        $this->addRoute('GET', '/api/puzzle/{id}', [$this, 'handleGetPuzzle']);
        $this->addRoute('POST', '/api/validate', [$this, 'handleValidateWord']);
        $this->addRoute('GET', '/api/themes', [$this, 'handleGetThemes']);

        // Score routes
        $this->addRoute('GET', '/api/scores', [$this, 'handleGetScores']);
        $this->addRoute('GET', '/api/scores/my', [$this, 'handleGetMyScores']);
        $this->addRoute('GET', '/api/scores/stats', [$this, 'handleGetScoreStats']);
        $this->addRoute('GET', '/api/scores/my/stats', [$this, 'handleGetMyScoreStats']);

        // Page routes
        $this->addRoute('GET', '/', [$this, 'handleHomePage']);
        $this->addRoute('GET', '/play', [$this, 'handlePlayPage']);
        $this->addRoute('GET', '/create', [$this, 'handleCreatePage']);
        $this->addRoute('GET', '/scores', [$this, 'handleScoresPage']);
        $this->addRoute('GET', '/profile', [$this, 'handleProfilePage']);
    }

    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function handleRequest(string $method, string $path): void
    {
        $path = parse_url($path, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                try {
                    $params = $this->extractPathParams($route['path'], $path);
                    call_user_func_array($route['handler'], $params);
                    return;
                } catch (\Exception $e) {
                    $this->sendErrorResponse($e->getMessage(), 500);
                    return;
                }
            }
        }
        
        $this->sendErrorResponse('Route not found', 404);
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));
        
        if (count($routeParts) !== count($requestParts)) {
            return false;
        }
        
        foreach ($routeParts as $i => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                continue; // Parameter placeholder
            }
            
            if ($routePart !== $requestParts[$i]) {
                return false;
            }
        }
        
        return true;
    }

    private function extractPathParams(string $routePath, string $requestPath): array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));
        $params = [];
        
        foreach ($routeParts as $i => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                $paramName = trim($routePart, '{}');
                $params[$paramName] = $requestParts[$i] ?? null;
            }
        }
        
        return array_values($params);
    }

    // Authentication handlers
    public function handleRegister(): void
    {
        $data = $this->getRequestData();
        
        try {
            $result = $this->authService->register(
                $data['username'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['first_name'] ?? '',
                $data['last_name'] ?? ''
            );
            
            $this->sendJsonResponse($result);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function handleLogin(): void
    {
        $data = $this->getRequestData();
        
        // Log the received login data
        error_log("Login request received - data: " . json_encode($data));
        
        try {
            $result = $this->authService->login(
                $data['username'] ?? '',
                $data['password'] ?? ''
            );
            
            error_log("Login successful for user: " . ($data['username'] ?? 'unknown'));
            $this->sendJsonResponse($result);
        } catch (\Exception $e) {
            error_log("Login failed with error: " . $e->getMessage());
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function handleLogout(): void
    {
        $token = $this->getAuthToken();
        
        try {
            $result = $this->authService->logout($token);
            $this->sendJsonResponse($result);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function handleGetProfile(): void
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            $this->sendErrorResponse('Unauthorized', 401);
            return;
        }
        
        try {
            $profile = $this->authService->getUserProfile($user['user_id']);
            $this->sendJsonResponse(['success' => true, 'profile' => $profile]);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    public function handleUpdateProfile(): void
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            $this->sendErrorResponse('Unauthorized', 401);
            return;
        }
        
        $data = $this->getRequestData();
        
        try {
            $result = $this->authService->updateProfile($user['user_id'], $data);
            $this->sendJsonResponse($result);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function handleChangePassword(): void
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            $this->sendErrorResponse('Unauthorized', 401);
            return;
        }
        
        $data = $this->getRequestData();
        
        try {
            $result = $this->authService->changePassword(
                $user['user_id'],
                $data['current_password'] ?? '',
                $data['new_password'] ?? ''
            );
            
            $this->sendJsonResponse($result);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    // Game handlers
    public function handleGeneratePuzzle(): void
    {
        $data = $this->getRequestData();
        $themeId = $data['theme_id'] ?? '';
        $options = $data['options'] ?? [];
        
        if (empty($themeId)) {
            $this->sendErrorResponse('No theme ID provided', 400);
            return;
        }
        
        try {
            // Determine grid size and word count based on difficulty
            $difficulty = $options['difficulty'] ?? 'medium';
            $gridSize = $difficulty === 'easy' ? 10 : ($difficulty === 'medium' ? 15 : 20);
            $wordCount = $gridSize; // Grid size always equals word count
            
            // Get ALL words from the theme service (we'll filter and randomize in the puzzle generator)
            $words = $this->themeService->getThemeWords($themeId);
            
            if (empty($words)) {
                $this->sendErrorResponse('No words available for this theme', 400);
                return;
            }
            
            // Add grid size and word count to options for the puzzle generator
            $options['size'] = $gridSize;
            $options['word_count'] = $wordCount;
            $puzzle = $this->puzzleGenerator->generatePuzzle($words, $options);
            
            // Debug logging
            error_log("Puzzle generated - Grid size: {$gridSize}, Target words: {$wordCount}, Actual words: " . count($puzzle['words']));
            
            // Create game record in database
            $user = $this->getAuthenticatedUser();
            $gameData = [
                'user_id' => $user['user_id'] ?? null,
                'puzzle_id' => $puzzle['id'],
                'theme' => $themeId,
                'difficulty' => $difficulty,
                'grid_size' => $gridSize,
                'total_words' => count($puzzle['words']), // Use actual word count from puzzle
                'puzzle_data' => $puzzle // Store complete puzzle data as JSON
            ];
            
            $gameId = $this->gameService->createGame($gameData);
            
            $this->sendJsonResponse([
                'success' => true,
                'id' => $puzzle['id'],
                'game_id' => $gameId,
                'puzzle' => $puzzle,
                'message' => 'Puzzle generated successfully'
            ]);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    public function handleGetPuzzle(string $id): void
    {
        try {
            $game = $this->gameService->getGameByPuzzleId($id);
            
            if (!$game) {
                $this->sendErrorResponse('Puzzle not found', 404);
                return;
            }
            
            // Return the puzzle data from the game record
            $this->sendJsonResponse([
                'success' => true,
                'puzzle' => $game['puzzle_data']
            ]);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    public function handleValidateWord(): void
    {
        $data = $this->getRequestData();
        $grid = $data['grid'] ?? [];
        $selection = $data['selection'] ?? [];
        $placedWords = $data['placed_words'] ?? [];
        
        try {
            $isValid = $this->puzzleGenerator->validateWordSelection($grid, $selection, $placedWords);
            
            $this->sendJsonResponse([
                'success' => true,
                'valid' => $isValid
            ]);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    public function handleGetThemes(): void
    {
        try {
            $themes = $this->themeService->getAvailableThemes();
            $stats = $this->themeService->getThemeStats();
            
            // Include word lists for each theme
            foreach ($themes as &$theme) {
                try {
                    $theme['words'] = $this->themeService->getThemeWords($theme['id']);
                } catch (\Exception $e) {
                    $theme['words'] = [];
                }
            }
            
            $this->sendJsonResponse([
                'success' => true,
                'themes' => $themes,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    // Score handlers (placeholder implementations)
    public function handleGetScores(): void
    {
        $this->sendJsonResponse([
            'success' => true,
            'scores' => [],
            'message' => 'Score system not yet implemented'
        ]);
    }

    public function handleGetMyScores(): void
    {
        $this->sendJsonResponse([
            'success' => true,
            'scores' => [],
            'message' => 'Score system not yet implemented'
        ]);
    }

    public function handleGetScoreStats(): void
    {
        $this->sendJsonResponse([
            'success' => true,
            'stats' => [],
            'message' => 'Score system not yet implemented'
        ]);
    }

    public function handleGetMyScoreStats(): void
    {
        $this->sendJsonResponse([
            'success' => true,
            'stats' => [],
            'message' => 'Score system not yet implemented'
        ]);
    }

    // Page handlers
    public function handleHomePage(): void
    {
        $this->renderPage('home');
    }

    public function handlePlayPage(): void
    {
        $this->renderPage('play');
    }

    public function handleCreatePage(): void
    {
        $this->renderPage('create');
    }

    public function handleScoresPage(): void
    {
        $this->renderPage('scores');
    }

    public function handleProfilePage(): void
    {
        $this->renderPage('profile');
    }

    // Helper methods
    private function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        error_log("Raw request input: " . $input);
        
        $decoded = json_decode($input, true);
        error_log("Decoded request data: " . json_encode($decoded));
        
        return $decoded ?? [];
    }

    private function getAuthToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getAuthenticatedUser(): ?array
    {
        $token = $this->getAuthToken();
        if (!$token) {
            return null;
        }
        
        return $this->authService->validateToken($token);
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function sendErrorResponse(string $message, int $statusCode = 400): void
    {
        $this->sendJsonResponse([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }

    private function renderPage(string $pageName): void
    {
        $pageFile = __DIR__ . '/../../public/views/' . $pageName . '.php';
        
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            $this->sendErrorResponse('Page not found', 404);
        }
    }
}
