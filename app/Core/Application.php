<?php

declare(strict_types=1);

namespace Sudoku\Core;

/**
 * Sudoku Game - Core Application Class
 * 
 * Main application class that handles routing, middleware, and application lifecycle.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */



use Sudoku\Controllers\AuthController;
use Sudoku\Controllers\GameController;
use Sudoku\Controllers\HomeController;
use Sudoku\Controllers\ScoreController;
use Sudoku\Controllers\DatabaseController;
use Sudoku\Controllers\DeployController;
use Sudoku\Controllers\TestingController;
use Sudoku\Controllers\AdminController;
use Sudoku\Middleware\AuthMiddleware;
use Sudoku\Services\DatabaseService;
use Sudoku\Services\SessionService;
use Exception;

/**
 * Main application class
 */
class Application
{
    private DatabaseService $database_service;
    private SessionService $session_service;
    private array $routes = [];

    public function __construct()
    {
        $this->database_service = new DatabaseService();
        $this->session_service = new SessionService($this->database_service, new \Sudoku\Services\LoggingService());
        $this->registerRoutes();
    }

    /**
     * Register all application routes
     */
    private function registerRoutes(): void
    {
        // Public routes
        $this->routes['GET']['/'] = [HomeController::class, 'index'];
        $this->routes['GET']['/how-to-play'] = [HomeController::class, 'howToPlay'];
        $this->routes['GET']['/login'] = [AuthController::class, 'showLogin'];
        $this->routes['POST']['/login'] = [AuthController::class, 'login'];
        $this->routes['GET']['/register'] = [AuthController::class, 'showRegister'];
        $this->routes['POST']['/register'] = [AuthController::class, 'register'];
        $this->routes['GET']['/forgot-password'] = [AuthController::class, 'showForgotPassword'];
        $this->routes['POST']['/forgot-password'] = [AuthController::class, 'forgotPassword'];
        $this->routes['GET']['/reset-password'] = [AuthController::class, 'showResetPassword'];
        $this->routes['POST']['/reset-password'] = [AuthController::class, 'resetPassword'];

        // Protected routes
        $this->routes['GET']['/dashboard'] = [HomeController::class, 'dashboard'];
        $this->routes['POST']['/logout'] = [AuthController::class, 'logout'];
        $this->routes['POST']['/revoke-trusted-device'] = [AuthController::class, 'revokeTrustedDevice'];
        $this->routes['POST']['/remove-trusted-device'] = [AuthController::class, 'removeTrustedDevice'];
        $this->routes['GET']['/game'] = [GameController::class, 'showGame'];
        $this->routes['GET']['/game/new'] = [GameController::class, 'newGame'];
        $this->routes['POST']['/game/new'] = [GameController::class, 'newGame'];
        $this->routes['GET']['/create'] = [HomeController::class, 'showCreate'];
        $this->routes['POST']['/game/save'] = [GameController::class, 'saveGame'];
        $this->routes['POST']['/game/load'] = [GameController::class, 'loadGame'];
        $this->routes['POST']['/game/validate'] = [GameController::class, 'validateMove'];
        $this->routes['POST']['/game/hint'] = [GameController::class, 'getHint'];
        $this->routes['POST']['/game/errors'] = [GameController::class, 'trackErrors'];
        $this->routes['POST']['/game/complete'] = [GameController::class, 'completeGame'];
        $this->routes['POST']['/game/track-completion'] = [GameController::class, 'trackCompletion'];
        $this->routes['POST']['/game/delete'] = [GameController::class, 'deleteGame'];
        $this->routes['GET']['/scores'] = [ScoreController::class, 'showScores'];
        $this->routes['GET']['/admin/deploy'] = [DeployController::class, 'showDeploy'];
        $this->routes['GET']['/profile'] = [AuthController::class, 'showProfile'];
        $this->routes['POST']['/profile/update'] = [AuthController::class, 'updateProfile'];
        // Database management routes
        $this->routes['GET']['/admin/database'] = [DatabaseController::class, 'index'];
        $this->routes['POST']['/admin/database/update'] = [DatabaseController::class, 'updateRecord'];
        $this->routes['POST']['/admin/database/delete'] = [DatabaseController::class, 'deleteRecord'];
        $this->routes['GET']['/admin/testing'] = [TestingController::class, 'index'];
        $this->routes['GET']['/admin/users'] = [AdminController::class, 'showUsers'];
        $this->routes['POST']['/admin/database/update-password'] = [DatabaseController::class, 'updatePassword'];

    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Normalize URI by removing trailing slash (except for root)
        if ($request_uri !== '/' && substr($request_uri, -1) === '/') {
            $request_uri = rtrim($request_uri, '/');
        }
        
        $full_uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Log request processing
        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            // error_log("Processing request: {$request_method} {$full_uri} -> {$request_uri}", 3, '/var/www/html/Logs/wordsearch_debug.log');
        }

        // Handle API routes
        if (str_starts_with($request_uri, '/api/')) {
            $this->handleApiRequest($request_method, $request_uri);
            return;
        }

        // Handle web routes
        $this->handleWebRequest($request_method, $request_uri);
    }

    /**
     * Handle web requests
     */
    private function handleWebRequest(string $method, string $uri): void
    {
        // Log web request handling
        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            // error_log("Handling web request: {$method} {$uri}", 3, '/var/www/html/Logs/wordsearch_debug.log');
            // error_log("Available routes for {$method}: " . json_encode(array_keys($this->routes[$method] ?? [])));
        }
        
        if (!isset($this->routes[$method][$uri])) {
            if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                // error_log("Route not found: {$method} {$uri}", 3, '/var/www/html/Logs/wordsearch_debug.log');
            }
            $this->render404();
            return;
        }

        [$controller_class, $method_name] = $this->routes[$method][$uri];
        
        // Check if route requires authentication
        if ($this->requiresAuth($uri)) {
            $auth_middleware = new AuthMiddleware();
            if (!$auth_middleware->handle()) {
                header('Location: /login');
                exit;
            }
        }

        try {
            $controller = new $controller_class($this->database_service, $this->session_service);
            $controller->$method_name();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle API requests
     */
    private function handleApiRequest(string $method, string $uri): void
    {
        header('Content-Type: application/json');
        
        // Extract API endpoint
        $endpoint = str_replace('/api', '', $uri);
        
        switch ($endpoint) {
            case '/themes':
                if ($method === 'GET') {
                    $controller = new \Sudoku\Controllers\ThemeController();
                    $controller->getThemes();
                }
                break;
            case '/game/state':
                if ($method === 'GET') {
                    $controller = new GameController($this->database_service, $this->session_service);
                    $controller->getGameState();
                }
                break;
            case '/game/move':
                if ($method === 'POST') {
                    $controller = new GameController($this->database_service, $this->session_service);
                    $controller->makeMove();
                }
                break;
            case '/scores/leaderboard':
                if ($method === 'GET') {
                    $controller = new ScoreController($this->database_service, $this->session_service);
                    $controller->getLeaderboard();
                }
                break;
            case '/admin/deploy/status':
                if ($method === 'GET') {
                    $controller = new DeployController($this->database_service, $this->session_service);
                    $controller->getDeploymentStatus();
                }
                break;
            case '/admin/deploy/tags':
                if ($method === 'GET') {
                    $controller = new DeployController($this->database_service, $this->session_service);
                    $controller->getAvailableTags();
                }
                break;
            case '/admin/deploy/execute':
                if ($method === 'POST') {
                    $controller = new DeployController($this->database_service, $this->session_service);
                    $controller->executeDeploy();
                }
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
        }
    }

    /**
     * Check if route requires authentication
     */
    private function requiresAuth(string $uri): bool
    {
        $protected_routes = [
            '/dashboard',
            '/game',
            '/game/new',
            '/scores',
            '/deploy',
            '/profile',
            '/admin'
        ];

        // Check exact match first
        if (in_array($uri, $protected_routes)) {
            return true;
        }
        
        // Check if URI starts with any protected route
        foreach ($protected_routes as $route) {
            if (str_starts_with($uri, $route)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Render 404 page
     */
    private function render404(): void
    {
        http_response_code(404);
        include __DIR__ . '/../../resources/views/errors/404.php';
    }
} 