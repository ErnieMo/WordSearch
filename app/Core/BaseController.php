<?php

declare(strict_types=1);

namespace Sudoku\Core;

/**
 * Sudoku Game - Base Controller
 * 
 * Base controller class providing common functionality for all controllers.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

use Sudoku\Services\DatabaseService;
use Sudoku\Services\SessionService;
use Sudoku\Services\SudokuService;
use Sudoku\Services\EmailService;
use Sudoku\Services\LoggingService;
use Exception;

/**
 * Base controller with common functionality
 */
abstract class BaseController
{
    protected DatabaseService $database_service;
    protected SessionService $session_service;
    protected SudokuService $sudoku_service;
    protected EmailService $email_service;
    protected LoggingService $logging_service;

    public function __construct(DatabaseService $database_service, SessionService $session_service)
    {
        $this->database_service = $database_service;
        $this->session_service = $session_service;
        $this->sudoku_service = new SudokuService();
        $this->email_service = new EmailService();
        $this->logging_service = new LoggingService();
    }

    /**
     * Send JSON response
     */
    protected function sendJsonResponse(array $data, int $status_code = 200): void
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send error JSON response
     */
    protected function sendErrorResponse(string $message, int $status_code = 400): void
    {
        $this->sendJsonResponse(['error' => $message], $status_code);
    }

    /**
     * Send success JSON response
     */
    protected function sendSuccessResponse(array $data = [], string $message = 'Success'): void
    {
        $this->sendJsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
    }

    /**
     * Redirect with flash message
     */
    protected function redirectWithFlash(string $url, string $type, string $message): void
    {
        $this->session_service->flash($type, $message);
        header("Location: $url");
        exit;
    }

    /**
     * Require authentication or redirect to login
     */
    protected function requireAuth(): void
    {
        if (!$this->session_service->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Get current user or redirect to login
     */
    protected function getCurrentUser(): array
    {
        $user = $this->session_service->getCurrentUser();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        return $user;
    }

    /**
     * Require admin access or redirect to dashboard
     */
    protected function requireAdmin(): void
    {
        if (!$this->session_service->isAdmin()) {
            $this->redirectWithFlash('/dashboard', 'error', 'Access denied. Administrator privileges required.');
        }
    }

    /**
     * Render view with layout
     */
    protected function render(string $view_path, array $data = []): void
    {
        // Extract data to variables for view
        extract($data);
        
        // Start output buffering
        ob_start();
        include __DIR__ . "/../../resources/views/$view_path.php";
        $content = ob_get_clean();
        
        // Include layout
        include __DIR__ . '/../../resources/views/layouts/app.php';
    }

    /**
     * Render view without layout
     */
    protected function renderPartial(string $view_path, array $data = []): void
    {
        extract($data);
        include __DIR__ . "/../../resources/views/$view_path.php";
    }

    /**
     * Log database operation
     */
    protected function logDatabaseOperation(string $operation, string $table, array $data = []): void
    {
        $this->logging_service->logDatabaseOperation($operation, $table, $data);
    }
} 