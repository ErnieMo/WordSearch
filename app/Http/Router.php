<?php

declare(strict_types=1);

namespace App\Http;

use App\Services\AuthService;
use App\Services\PuzzleGenerator;
use App\Services\ThemeService;
use App\Services\GameService;
use App\Services\DatabaseService;
use PDO;

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
        $this->addRoute('POST', '/api/scores/save', [$this, 'handleSaveScore']);

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
        
        // Debug logging
        error_log("Router: Handling request - Method: $method, Path: $path");
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                error_log("Router: Route matched! Calling handler: " . get_class($route['handler'][0]) . "::" . $route['handler'][1]);
                try {
                    $params = $this->extractPathParams($route['path'], $path);
                    call_user_func_array($route['handler'], $params);
                    return;
                } catch (\Exception $e) {
                    error_log("Router: Handler error: " . $e->getMessage());
                    $this->sendErrorResponse($e->getMessage(), 500);
                    return;
                }
            }
        }
        
        error_log("Router: No route found for $method $path");
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
        $options = $data['options'] ?? '';
        
        if (empty($themeId)) {
            $this->sendErrorResponse('No theme ID provided', 400);
            return;
        }
        
        // Check if user is authenticated (optional - guest users can play)
        $user = $this->getAuthenticatedUser();
        
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
            
            // Create game record in database (user_id may be null for guest users)
            $gameData = [
                'user_id' => $user['user_id'] ?? null, // May be null for guest users
                'puzzle_id' => $puzzle['id'],
                'theme' => $themeId,
                'difficulty' => $difficulty,
                'grid_size' => $gridSize,
                'total_words' => count($puzzle['words']), // Use actual word count from puzzle
                'puzzle_data' => $puzzle // Store complete puzzle data as JSON
            ];
            
            $gameId = $this->gameService->createGame($gameData);
            
            // Save guest user preferences for future visits
            if (!$user) {
                $_SESSION['guest_last_theme'] = $themeId;
                $_SESSION['guest_last_difficulty'] = $difficulty;
                $_SESSION['guest_last_diagonals'] = $options['diagonals'] ?? true;
                $_SESSION['guest_last_reverse'] = $options['reverse'] ?? false;
                error_log("Saved guest preferences - Theme: $themeId, Difficulty: $difficulty");
            }
            
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

    // Score handlers
    public function handleGetScores(): void
    {
        try {
            // Get query parameters for filtering
            $theme = $_GET['theme'] ?? '';
            $difficulty = $_GET['difficulty'] ?? '';
            $timeRange = $_GET['timeRange'] ?? 'all';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            // Build the base query for completed games
            $whereConditions = ["g.status = 'completed'", "g.completion_time > 0"];
            $params = [];
            
            if ($theme) {
                $whereConditions[] = "g.theme = :theme";
                $params['theme'] = $theme;
            }
            
            if ($difficulty) {
                $whereConditions[] = "g.difficulty = :difficulty";
                $params['difficulty'] = $difficulty;
            }
            
            // Add time range filtering
            switch ($timeRange) {
                case 'today':
                    $whereConditions[] = "DATE(g.end_time) = CURRENT_DATE";
                    break;
                case 'week':
                    $whereConditions[] = "g.end_time >= CURRENT_DATE - INTERVAL '7 days'";
                    break;
                case 'month':
                    $whereConditions[] = "g.end_time >= CURRENT_DATE - INTERVAL '30 days'";
                    break;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(*) FROM games g WHERE $whereClause";
            $countStmt = $this->dbService->query($countSql, $params);
            $totalScores = $countStmt->fetchColumn();
            
            // Get scores with user information
            $scoresSql = "
                SELECT 
                    g.id,
                    g.puzzle_id,
                    g.theme,
                    g.difficulty,
                    g.completion_time as elapsed_time,
                    g.hints_used,
                    g.words_found,
                    g.total_words,
                    g.end_time as created_at,
                    COALESCE(u.username, 'Guest') as username
                FROM games g
                LEFT JOIN users u ON g.user_id = u.id
                WHERE $whereClause
                ORDER BY g.completion_time ASC, g.hints_used ASC, g.end_time DESC
                LIMIT $perPage OFFSET $offset
            ";
            
            $scoresStmt = $this->dbService->query($scoresSql, $params);
            $scores = $scoresStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log("Scores query executed. Found " . count($scores) . " scores out of $totalScores total");
            if (count($scores) > 0) {
                error_log("First score sample: " . json_encode($scores[0]));
            }
            
            // Calculate pagination info
            $totalPages = ceil($totalScores / $perPage);
            
            $this->sendJsonResponse([
                'success' => true,
                'scores' => $scores,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_scores' => $totalScores,
                    'per_page' => $perPage
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Error fetching scores: " . $e->getMessage());
            $this->sendErrorResponse('Failed to fetch scores', 500);
        }
    }

    public function handleGetMyScores(): void
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->sendErrorResponse('Authentication required', 401);
                return;
            }
            
            // Get user's completed games
            $scoresSql = "
                SELECT 
                    g.id,
                    g.puzzle_id,
                    g.theme,
                    g.difficulty,
                    g.completion_time as elapsed_time,
                    g.hints_used,
                    g.words_found,
                    g.total_words,
                    g.end_time as created_at
                FROM games g
                WHERE g.user_id = :user_id AND g.status = 'completed'
                ORDER BY g.end_time DESC
                LIMIT 50
            ";
            
            $scoresStmt = $this->dbService->query($scoresSql, ['user_id' => $user['user_id']]);
            $scores = $scoresStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendJsonResponse([
                'success' => true,
                'scores' => $scores
            ]);
            
        } catch (\Exception $e) {
            error_log("Error fetching user scores: " . $e->getMessage());
            $this->sendErrorResponse('Failed to fetch user scores', 500);
        }
    }

    public function handleGetScoreStats(): void
    {
        try {
            // Get overall game statistics
            $statsSql = "
                SELECT 
                    COUNT(*) as total_games,
                    COUNT(DISTINCT g.user_id) as total_players,
                    AVG(g.completion_time) as avg_time,
                    MIN(g.completion_time) as best_time,
                    AVG(g.hints_used) as avg_hints
                FROM games g
                WHERE g.status = 'completed' AND g.completion_time > 0
            ";
            
            $statsStmt = $this->dbService->query($statsSql);
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get theme popularity
            $themeSql = "
                SELECT 
                    theme,
                    COUNT(*) as game_count
                FROM games
                WHERE status = 'completed'
                GROUP BY theme
                ORDER BY game_count DESC
            ";
            
            $themeStmt = $this->dbService->query($themeSql);
            $themeStats = $themeStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendJsonResponse([
                'success' => true,
                'stats' => [
                    'total_games' => intval($stats['total_games']),
                    'total_players' => intval($stats['total_players']),
                    'avg_time' => round(floatval($stats['avg_time']), 1),
                    'best_time' => intval($stats['best_time']),
                    'avg_hints' => round(floatval($stats['avg_hints']), 1),
                    'theme_popularity' => $themeStats
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Error fetching score stats: " . $e->getMessage());
            $this->sendErrorResponse('Failed to fetch score statistics', 500);
        }
    }

    public function handleGetMyScoreStats(): void
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->sendErrorResponse('Authentication required', 401);
                return;
            }
            
            // Get user's comprehensive game statistics
            $statsSql = "
                SELECT 
                    COUNT(*) as total_games,
                    AVG(CASE WHEN g.completion_time > 0 THEN g.completion_time END) as avg_time,
                    SUM(g.hints_used) as total_hints,
                    COUNT(CASE WHEN g.status = 'completed' THEN 1 END) as completed_games
                FROM games g
                WHERE g.user_id = :user_id
            ";
            
            $statsStmt = $this->dbService->query($statsSql, ['user_id' => $user['user_id']]);
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log("User stats query executed for user " . $user['user_id'] . ": " . json_encode($stats));
            
            // Get best times for each difficulty
            $bestTimesSql = "
                SELECT 
                    difficulty,
                    MIN(completion_time) as best_time
                FROM games
                WHERE user_id = :user_id AND status = 'completed' AND completion_time > 0
                GROUP BY difficulty
            ";
            
            $bestTimesStmt = $this->dbService->query($bestTimesSql, ['user_id' => $user['user_id']]);
            $bestTimes = $bestTimesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log("Best times query executed for user " . $user['user_id'] . ": " . json_encode($bestTimes));
            
            // Organize best times by difficulty
            $bestTimeEasy = null;
            $bestTimeMedium = null;
            $bestTimeHard = null;
            $bestTimeExpert = null;
            
            foreach ($bestTimes as $time) {
                switch ($time['difficulty']) {
                    case 'easy':
                        $bestTimeEasy = intval($time['best_time']);
                        break;
                    case 'medium':
                        $bestTimeMedium = intval($time['best_time']);
                        break;
                    case 'hard':
                        $bestTimeHard = intval($time['best_time']);
                        break;
                    case 'expert':
                        $bestTimeExpert = intval($time['best_time']);
                        break;
                }
            }
            
            // Calculate success rate
            $totalGames = intval($stats['total_games']);
            $completedGames = intval($stats['completed_games']);
            $successRate = $totalGames > 0 ? round(($completedGames / $totalGames) * 100) : 0;
            
            $this->sendJsonResponse([
                'success' => true,
                'stats' => [
                    'total_games' => $totalGames,
                    'avg_time' => round(floatval($stats['avg_time']), 1),
                    'total_hints' => intval($stats['total_hints']),
                    'success_rate' => $successRate,
                    'best_time_easy' => $bestTimeEasy,
                    'best_time_medium' => $bestTimeMedium,
                    'best_time_hard' => $bestTimeHard,
                    'best_time_expert' => $bestTimeExpert
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Error fetching user stats: " . $e->getMessage());
            $this->sendErrorResponse('Failed to fetch user statistics', 500);
        }
    }

    // Page handlers
    public function handleHomePage(): void
    {
        // Get user preferences for smart defaults
        $userPrefs = $this->getUserPreferencesForHome();
        $this->renderPage('home', $userPrefs);
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
        // First try to get user from session (no database call)
        $user = $this->authService->getCurrentUser();
        if ($user) {
            return $user;
        }
        
        // If no session, try JWT token
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

    private function getUserPreferencesForHome(): array
    {
        $defaults = [
            'theme' => 'animals',
            'difficulty' => 'medium',
            'diagonals' => true,
            'reverse' => false
        ];
        
        // Try to get user from session first
        $user = $this->authService->getCurrentUser();
        if ($user) {
            // User is logged in - get preferences from session
            $defaults['theme'] = $_SESSION['default_theme'] ?? 'animals';
            $defaults['difficulty'] = $_SESSION['default_level'] ?? 'medium';
            $defaults['diagonals'] = $_SESSION['default_diagonals'] ?? true;
            $defaults['reverse'] = $_SESSION['default_reverse'] ?? false;
            error_log("Using logged-in user preferences - Theme: {$defaults['theme']}, Difficulty: {$defaults['difficulty']}");
        } else {
            // Check for guest user preferences in session
            $defaults['theme'] = $_SESSION['guest_last_theme'] ?? 'animals';
            $defaults['difficulty'] = $_SESSION['guest_last_difficulty'] ?? 'medium';
            $defaults['diagonals'] = $_SESSION['guest_last_diagonals'] ?? true;
            $defaults['reverse'] = $_SESSION['guest_last_reverse'] ?? false;
            error_log("Using guest user preferences - Theme: {$defaults['theme']}, Difficulty: {$defaults['difficulty']}");
        }
        
        return $defaults;
    }

    private function renderPage(string $pageName, array $data = []): void
    {
        $pageFile = __DIR__ . '/../../public/views/' . $pageName . '.php';
        
        if (file_exists($pageFile)) {
            // Extract data to variables for the view
            extract($data);
            include $pageFile;
        } else {
            $this->sendErrorResponse('Page not found', 404);
        }
    }

    // Score saving handler
    public function handleSaveScore(): void
    {
        $data = $this->getRequestData();
        $gameId = $data['game_id'] ?? null;
        $completionTime = $data['completion_time'] ?? null;
        $hintsUsed = $data['hints_used'] ?? 0;
        
        if (!$gameId || !$completionTime) {
            $this->sendErrorResponse('Missing required data: game_id and completion_time', 400);
            return;
        }

        try {
            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->sendErrorResponse('Authentication required to save score', 401);
                return;
            }

            error_log("=== SAVE SCORE DEBUG ===");
            error_log("User authenticated: " . json_encode($user));
            error_log("Game ID: " . $gameId);

            // Update the game record with completion data and user ID
            $updateData = [
                'user_id' => $user['user_id'],
                'completion_time' => $completionTime,
                'elapsed_time' => $completionTime, // Set elapsed_time to completion_time
                'hints_used' => $hintsUsed,
                'completed_at' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ];

            error_log("About to call updateGame with: " . json_encode($updateData));
            
            $result = $this->gameService->updateGame($gameId, $updateData);
            
            error_log("updateGame result: " . ($result ? 'true' : 'false'));

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Score saved successfully',
                'score' => [
                    'completion_time' => $completionTime,
                    'hints_used' => $hintsUsed,
                    'user_id' => $user['user_id']
                ]
            ]);
            
            error_log("Response sent successfully");
        } catch (\Exception $e) {
            error_log("Exception in handleSaveScore: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendErrorResponse('Failed to save score: ' . $e->getMessage(), 500);
        }
    }
}
