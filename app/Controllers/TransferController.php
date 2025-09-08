<?php
declare(strict_types=1);

/**
 * Transfer Controller for Token-Based Cross-Site Authentication
 * 
 * @author WordSearch Game Team
 * @last_modified 2024-01-01
 */

// Log file inclusion for debugging
error_log(__FILE__ . PHP_EOL, 3, __DIR__ . '/../../log/included_files.log');

use App\Services\DatabaseService;
use App\Services\AuthService;

class TransferController
{
    private DatabaseService $db;
    private AuthService $auth;
    private TransferTokenService $tokenService;

    public function __construct()
    {
        $this->db = new DatabaseService();
        $this->auth = new AuthService($this->db);
        $this->tokenService = new TransferTokenService($this->db);
    }

    /**
     * Handle token-based login transfer
     */
    public function transferLogin(): void
    {
        try {
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                $this->showError('No transfer token provided');
                return;
            }

            // Validate token and get user data
            $user = $this->tokenService->validateTransferToken($token);
            
            if (!$user) {
                $this->showError('Invalid or expired transfer token');
                return;
            }

            // Log the user in
            $this->loginUser($user);
            
            // Redirect to dashboard with success message
            $this->redirectWithSuccess($user);

        } catch (Exception $e) {
            error_log("Transfer login error: " . $e->getMessage());
            $this->showError('Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate transfer token for current user
     */
    public function generateTransferToken(): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = $this->auth->getCurrentUser();
            
            if (!$user) {
                throw new RuntimeException('User not logged in');
            }

            // Generate transfer token
            $token = $this->tokenService->generateTransferToken($user['id'], 'wordsearch');
            
            // Create transfer URLs for both other apps
            $tileSliderUrl = $this->tokenService->createTransferUrl($token, 'tileslider');
            $sudokuUrl = $this->tokenService->createTransferUrl($token, 'sudoku');
            
            echo json_encode([
                'success' => true,
                'token' => $token,
                'transfer_urls' => [
                    'tileslider' => $tileSliderUrl,
                    'sudoku' => $sudokuUrl
                ],
                'expires_in' => 300 // 5 minutes
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get transfer token info
     */
    public function getTokenInfo(): void
    {
        header('Content-Type: application/json');
        
        try {
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                throw new RuntimeException('No token provided');
            }

            $user = $this->tokenService->validateTransferToken($token);
            
            if (!$user) {
                throw new RuntimeException('Invalid or expired token');
            }

            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get current session info
     */
    public function getSessionInfo(): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = $this->auth->getCurrentUser();
            
            echo json_encode([
                'success' => true,
                'session_id' => session_id(),
                'user' => $user,
                'is_logged_in' => $this->auth->isLoggedIn()
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Login user with transferred data
     */
    private function loginUser(array $user): void
    {
        // Set session data
        $_SESSION['user'] = $user;
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['transferred_from'] = 'external';
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Redirect with success message
     */
    private function redirectWithSuccess(array $user): void
    {
        $message = urlencode("Successfully logged in as {$user['username']}!");
        header("Location: /dashboard?transfer_success=1&message=" . $message);
        exit;
    }

    /**
     * Show error page
     */
    private function showError(string $message): void
    {
        http_response_code(400);
        include __DIR__ . '/../../resources/views/errors/transfer-error.php';
        exit;
    }
}
