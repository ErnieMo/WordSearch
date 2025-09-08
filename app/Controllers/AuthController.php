<?php

declare(strict_types=1);

namespace Sudoku\Controllers;

/**
 * Sudoku - Authentication Controller
 * 
 * Handles user authentication, registration, and password recovery.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

use Sudoku\Core\BaseController;
use Sudoku\Services\CookieService;
use Sudoku\Services\DatabaseService;
use Sudoku\Services\SessionService;
use Exception;

/**
 * Authentication controller
 */
class AuthController extends BaseController
{
    private CookieService $cookie_service;

    public function __construct(DatabaseService $database_service, SessionService $session_service)
    {
        parent::__construct($database_service, $session_service);
        $this->cookie_service = new CookieService();
    }

    /**
     * Show login page
     */
    public function showLogin(): void
    {
        // Check for access_token parameter for cross-site login
        $accessToken = $_GET['access_token'] ?? '';
        if (!empty($accessToken)) {
            $this->handleAccessTokenLogin($accessToken);
            return;
        }

        if ($this->session_service->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        // Check for trusted device cookie
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("=== SHOW LOGIN DEBUG ===");
            error_log("Checking for trusted device cookie: " . ($_COOKIE['trusted_device'] ?? 'NOT SET'));
        }
        
        $trusted_device_data = $this->cookie_service->validateTrustedDeviceCookie();
        
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("Trusted device validation result: " . ($trusted_device_data ? 'SUCCESS' : 'FAILED'));
            if ($trusted_device_data) {
                error_log("User ID: " . $trusted_device_data['user_id']);
                error_log("Email: " . $trusted_device_data['email']);
            }
        }
        
        if ($trusted_device_data) {
            // Auto-login with trusted device
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Proceeding with auto-login...");
            }
            $this->autoLoginWithTrustedDevice($trusted_device_data);
            return;
        }

        $flash_messages = $this->session_service->getAllFlash();
        include __DIR__ . '/../../resources/views/auth/login.php';
    }

    /**
     * Handle user login
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithFlash('/login', 'error', 'Invalid request method');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $trust_device = isset($_POST['trust_device']) && $_POST['trust_device'] === '1';
        
        // Extract device fingerprint data if available
        $fingerprint_data = null;
        if (isset($_POST['device_fingerprint']) && !empty($_POST['device_fingerprint'])) {
            // Try to get full fingerprint data from session storage
            $fingerprint_data = $this->extractFingerprintData();
        }

        $this->logging_service->logToDatabase('LOGIN_ATTEMPT', [
            'email' => $email,
            'password_length' => strlen($password),
            'trust_device' => $trust_device,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        if (empty($email) || empty($password)) {
            $this->logging_service->logToDatabase('LOGIN_VALIDATION_FAILED', [
                'error' => 'Empty fields',
                'email_empty' => empty($email),
                'password_empty' => empty($password)
            ]);
            $this->redirectWithFlash('/login', 'error', 'Email and password are required');
            return;
        }

        try {
            $this->logging_service->logToDatabase('LOGIN_USER_LOOKUP', [
                'email' => $email,
                'action' => 'searching_for_user'
            ]);

            $user = $this->database_service->findOne('users', ['email' => $email]);

            if (!$user) {
                $this->logging_service->logToDatabase('LOGIN_USER_NOT_FOUND', [
                    'email' => $email,
                    'error' => 'No user found with this email'
                ]);
                $this->redirectWithFlash('/login', 'error', 'Invalid email or password');
                return;
            }

            $this->logging_service->logToDatabase('LOGIN_USER_FOUND', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $email,
                'password_verification_result' => password_verify($password, $user['password'])
            ]);

            if (!password_verify($password, $user['password'])) {
                $this->logging_service->logToDatabase('LOGIN_PASSWORD_VERIFICATION_FAILED', [
                    'user_id' => $user['id'],
                    'email' => $email,
                    'error' => 'Password verification failed'
                ]);
                $this->redirectWithFlash('/login', 'error', 'Invalid email or password');
                return;
            }

            $this->logging_service->logToDatabase('LOGIN_SUCCESS', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $email,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            $this->session_service->setUserSession($user);
            $this->logDatabaseOperation('login', 'users', ['user_id' => $user['id']]);

            // Create trusted device cookie if requested
            if ($trust_device) {
                // Debug logging for trusted device creation
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("=== TRUSTED DEVICE CREATION DEBUG ===");
                    error_log("User ID: " . $user['id']);
                    error_log("Email: " . $email);
                    error_log("Fingerprint data available: " . ($fingerprint_data ? 'Yes' : 'No'));
                    if ($fingerprint_data) {
                        error_log("Fingerprint fields: " . implode(', ', array_keys($fingerprint_data)));
                    }
                }
                
                $result = $this->cookie_service->createTrustedDeviceCookie($user['id'], $email, $fingerprint_data);
                
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Trusted device cookie creation result: " . ($result ? 'Success' : 'Failed'));
                    error_log("=== END TRUSTED DEVICE DEBUG ===");
                }
                
                $this->logging_service->logToDatabase('TRUSTED_DEVICE_COOKIE_CREATED', [
                    'user_id' => $user['id'],
                    'email' => $email,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'fingerprint_available' => $fingerprint_data !== null
                ]);
            }

            // Log successful login to user_logins table AFTER trusted device creation
            // This ensures we have the enhanced device name if available
            $this->logUserLogin($user['id'], $fingerprint_data);

            header('Location: /dashboard');
            exit;
        } catch (Exception $e) {
            $this->logging_service->logToDatabase('LOGIN_EXCEPTION', [
                'email' => $email,
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            $this->redirectWithFlash('/login', 'error', 'Login failed. Please try again.');
        }
    }

    /**
     * Auto-login with trusted device cookie
     */
    private function autoLoginWithTrustedDevice(array $trusted_device_data): void
    {
        try {
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_AUTO_LOGIN_ATTEMPT', [
                'user_id' => $trusted_device_data['user_id'],
                'email' => $trusted_device_data['email'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Get user data from database
            $user = $this->database_service->findOne('users', ['id' => $trusted_device_data['user_id']]);
            
            if (!$user || $user['email'] !== $trusted_device_data['email']) {
                // Invalid trusted device data, remove cookie and show login
                $this->cookie_service->removeTrustedDeviceCookie();
                $this->logging_service->logToDatabase('TRUSTED_DEVICE_AUTO_LOGIN_FAILED', [
                    'user_id' => $trusted_device_data['user_id'],
                    'email' => $trusted_device_data['email'],
                    'error' => 'User not found or email mismatch'
                ]);
                $flash_messages = $this->session_service->getAllFlash();
                include __DIR__ . '/../../resources/views/auth/login.php';
                return;
            }

            // Set user session and show trusted device message instead of immediate redirect
            $this->session_service->setUserSession($user);
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_AUTO_LOGIN_SUCCESS', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Show trusted device message with option to remove preference
            $this->showTrustedDeviceMessage($user, $trusted_device_data);
            return;
            
        } catch (Exception $e) {
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_AUTO_LOGIN_EXCEPTION', [
                'user_id' => $trusted_device_data['user_id'] ?? 'unknown',
                'exception_message' => $e->getMessage()
            ]);
            
            // Remove invalid cookie and show login
            $this->cookie_service->removeTrustedDeviceCookie();
            $flash_messages = $this->session_service->getAllFlash();
            include __DIR__ . '/../../resources/views/auth/login.php';
        }
    }

    /**
     * Show trusted device message with option to remove preference
     */
    private function showTrustedDeviceMessage(array $user, array $trusted_device_data): void
    {
        // Get the actual trusted device record from database for display
        $trusted_device_record = $this->database_service->findOne('trusted_devices', [
            'user_id' => $trusted_device_data['user_id'],
            'cookie_token' => $trusted_device_data['device_token']
        ]);
        
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("Trusted device record for display: " . ($trusted_device_record ? 'FOUND' : 'NOT FOUND'));
            if ($trusted_device_record) {
                error_log("Device name: " . ($trusted_device_record['device_name'] ?? 'NOT SET'));
                error_log("IP address: " . ($trusted_device_record['ip_address'] ?? 'NOT SET'));
                error_log("Expires at: " . ($trusted_device_record['expires_at'] ?? 'NOT SET'));
            }
        }
        
        $flash_messages = $this->session_service->getAllFlash();
        include __DIR__ . '/../../resources/views/auth/trusted-device-message.php';
    }

    /**
     * Remove trusted device preference
     */
    public function removeTrustedDevice(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithFlash('/dashboard', 'error', 'Invalid request method');
            return;
        }

        if (!$this->session_service->isAuthenticated()) {
            $this->redirectWithFlash('/login', 'error', 'You must be logged in to remove trusted device');
            return;
        }

        try {
            // Remove the trusted device cookie
            $this->cookie_service->removeTrustedDeviceCookie();
            
            // Deactivate the trusted device in database
            if (isset($_COOKIE['trusted_device'])) {
                $this->deactivateTrustedDeviceInDatabase($_COOKIE['trusted_device']);
            }

            $this->logging_service->logToDatabase('TRUSTED_DEVICE_REMOVED', [
                'user_id' => $this->session_service->getCurrentUser()['id'],
                'action' => 'user_removed_trusted_device'
            ]);

            $this->redirectWithFlash('/dashboard', 'success', 'Trusted device preference removed. You will need to login again on this device.');
            
        } catch (Exception $e) {
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_REMOVE_ERROR', [
                'user_id' => $this->session_service->getCurrentUser()['id'] ?? 'unknown',
                'exception_message' => $e->getMessage()
            ]);
            
            $this->redirectWithFlash('/dashboard', 'error', 'Failed to remove trusted device preference. Please try again.');
        }
    }

    /**
     * Deactivate trusted device in database
     */
    private function deactivateTrustedDeviceInDatabase(string $cookie_token): void
    {
        try {
            $this->database_service->updateOne('trusted_devices', 
                ['cookie_token' => $cookie_token], 
                ['is_active' => false, 'updated_at' => $this->database_service->createDate()]
            );
        } catch (Exception $e) {
            // Log error but don't fail the operation
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_DEACTIVATE_ERROR', [
                'cookie_token' => substr($cookie_token, 0, 10) . '...',
                'exception_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show registration page
     */
    public function showRegister(): void
    {
        if ($this->session_service->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        $flash_messages = $this->session_service->getAllFlash();
        include __DIR__ . '/../../resources/views/auth/register.php';
    }

    /**
     * Handle user registration
     */
    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $this->redirectWithFlash('/register', 'error', 'All fields are required');
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            $this->redirectWithFlash('/register', 'error', 'Username must be between 3 and 50 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithFlash('/register', 'error', 'Please enter a valid email address');
        }

        if (strlen($password) < 6) {
            $this->redirectWithFlash('/register', 'error', 'Password must be at least 6 characters');
        }

        if ($password !== $confirm_password) {
            $this->redirectWithFlash('/register', 'error', 'Passwords do not match');
        }

        try {
            // Check if username already exists
            $existing_user = $this->database_service->findOne('users', ['username' => $username]);
            if ($existing_user) {
                $this->redirectWithFlash('/register', 'error', 'Username already exists');
            }

            // Check if email already exists
            $existing_email = $this->database_service->findOne('users', ['email' => $email]);
            if ($existing_email) {
                $this->redirectWithFlash('/register', 'error', 'Email already registered');
            }

            // Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_data = [
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'created_at' => $this->database_service->createDate(),
                'updated_at' => $this->database_service->createDate()
            ];

            $user_id = $this->database_service->insertOne('users', $user_data);
            $this->logDatabaseOperation('register', 'users', ['user_id' => $user_id]);

            $this->redirectWithFlash('/login', 'success', 'Registration successful! Please log in.');
        } catch (Exception $e) {
            $this->redirectWithFlash('/register', 'error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Handle user logout
     */
    public function logout(): void
    {
        // Note: We do NOT remove the trusted device cookie on logout
        // This allows users to stay logged in across subdomains even after logout
        // The trusted device cookie will persist until it expires (30 days) or is manually cleared
        
        $this->session_service->clearSession();
        header('Location: /');
        exit;
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword(): void
    {
        $flash_messages = $this->session_service->getAllFlash();
        include __DIR__ . '/../../resources/views/auth/forgot-password.php';
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(): void
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithFlash('/forgot-password', 'error', 'Please enter a valid email address');
        }

        try {
            $user = $this->database_service->findOne('users', ['email' => $email]);

            if (!$user) {
                // Don't reveal if email exists or not for security
                $this->redirectWithFlash('/forgot-password', 'success', 'If the email exists, a password reset link has been sent.');
            }

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update_data = [
                'reset_token' => $reset_token,
                'reset_expires' => $reset_expires,
                'updated_at' => $this->database_service->createDate()
            ];

            $this->database_service->updateOne('users', ['email' => $email], $update_data);
            $this->logDatabaseOperation('forgot_password', 'users', ['email' => $email]);

            // Send reset email
            $reset_url = $_ENV['APP_URL'] . '/reset-password?token=' . $reset_token;
            $email_sent = $this->email_service->sendPasswordResetEmail($email, $reset_url);

            if ($email_sent) {
                $this->redirectWithFlash('/forgot-password', 'success', 'Password reset link sent to your email');
            } else {
                $this->redirectWithFlash('/forgot-password', 'error', 'Failed to send reset email. Please try again.');
            }
        } catch (Exception $e) {
            $this->redirectWithFlash('/forgot-password', 'error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->redirectWithFlash('/forgot-password', 'error', 'Invalid reset link');
        }

        $flash_messages = $this->session_service->getAllFlash();
        include __DIR__ . '/../../resources/views/auth/reset-password.php';
    }

    /**
     * Handle password reset
     */
    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $this->logging_service->logToDatabase('RESET_PASSWORD_ATTEMPT', [
            'token' => substr($token, 0, 10) . "...",
            'password_length' => strlen($password),
            'confirm_password_length' => strlen($confirm_password)
        ]);

        if (empty($token) || empty($password) || empty($confirm_password)) {
            $this->logging_service->logToDatabase('RESET_PASSWORD_VALIDATION_FAILED', [
                'error' => 'Empty fields',
                'token_empty' => empty($token),
                'password_empty' => empty($password),
                'confirm_password_empty' => empty($confirm_password)
            ]);
            $this->redirectWithFlash('/reset-password?token=' . $token, 'error', 'All fields are required');
        }

        if (strlen($password) < 6) {
            $this->redirectWithFlash('/reset-password?token=' . $token, 'error', 'Password must be at least 6 characters');
        }

        if ($password !== $confirm_password) {
            $this->redirectWithFlash('/reset-password?token=' . $token, 'error', 'Passwords do not match');
        }

        try {
            $this->logging_service->logToDatabase('RESET_PASSWORD_USER_LOOKUP', [
                'token' => substr($token, 0, 10) . "...",
                'action' => 'searching_for_user'
            ]);
            
            // First find the user with the token
            $user = $this->database_service->findOne('users', [
                'reset_token' => $token
            ]);

            if (!$user) {
                $this->logging_service->logToDatabase('RESET_PASSWORD_USER_NOT_FOUND', [
                    'token' => substr($token, 0, 10) . "...",
                    'error' => 'No user found with token'
                ]);
                $this->redirectWithFlash('/forgot-password', 'error', 'Invalid reset link');
            }

            $this->logging_service->logToDatabase('RESET_PASSWORD_USER_FOUND', [
                'user_id' => $user['id'],
                'reset_expires' => $user['reset_expires'],
                'current_time' => date('Y-m-d H:i:s')
            ]);

            // Check if the token has expired
            if (strtotime($user['reset_expires']) < time()) {
                $this->logging_service->logToDatabase('RESET_PASSWORD_TOKEN_EXPIRED', [
                    'user_id' => $user['id'],
                    'reset_expires' => $user['reset_expires'],
                    'current_time' => date('Y-m-d H:i:s')
                ]);
                $this->redirectWithFlash('/forgot-password', 'error', 'Reset link has expired');
            }

            $this->logging_service->logToDatabase('RESET_PASSWORD_PROCEEDING', [
                'user_id' => $user['id'],
                'token' => substr($token, 0, 10) . "...",
                'action' => 'proceeding_with_password_reset'
            ]);
            // User found and token is valid, proceed with password reset

            // Update password and clear reset token
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_data = [
                'password' => $hashed_password,
                'reset_token' => null,
                'reset_expires' => null,
                'updated_at' => $this->database_service->createDate()
            ];

            $this->database_service->updateOne('users', ['id' => $user['id']], $update_data);
            $this->logDatabaseOperation('reset_password', 'users', ['user_id' => $user['id']]);

            $this->logging_service->logToDatabase('RESET_PASSWORD_SUCCESS', [
                'user_id' => $user['id'],
                'token' => substr($token, 0, 10) . "...",
                'action' => 'password_reset_successful'
            ]);
            $this->redirectWithFlash('/login', 'success', 'Password reset successful! Please log in with your new password.');
        } catch (Exception $e) {
            $this->logging_service->logToDatabase('RESET_PASSWORD_EXCEPTION', [
                'token' => substr($token, 0, 10) . "...",
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            $this->redirectWithFlash('/reset-password?token=' . $token, 'error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Show user profile
     */
    public function showProfile(): void
    {
        $user = $this->getCurrentUser();
        $flash_messages = $this->session_service->getAllFlash();
        
        // Get the user's last login information from user_logins table
        try {
            $last_login = $this->database_service->findOne('user_logins', [
                'user_id' => $user['id'],
                'success' => true
            ], ['order_by' => 'login_timestamp DESC']);
            
            if ($last_login) {
                $user['last_login_at'] = $last_login['login_timestamp'];
            }
        } catch (Exception $e) {
            // If there's an error, just continue without last login info
            error_log('Error fetching last login for profile: ' . $e->getMessage());
        }
        
        $this->render('auth/profile', [
            'user' => $user,
            'flash_messages' => $flash_messages
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(): void
    {
        $user = $this->getCurrentUser();
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $this->logging_service->logToDatabase('PROFILE_UPDATE_REQUEST', [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'request_timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'form_data' => [
                'username' => $username,
                'email' => $email,
                'has_current_password' => !empty($current_password),
                'has_new_password' => !empty($new_password),
                'has_confirm_password' => !empty($confirm_password)
            ]
        ]);

        if (empty($username) || empty($email)) {
            $this->logging_service->logToDatabase('PROFILE_UPDATE_VALIDATION_ERROR', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'error' => 'Username and email are required',
                'validation_failed' => true,
                'missing_fields' => [
                    'username' => empty($username),
                    'email' => empty($email)
                ]
            ]);
            $this->redirectWithFlash('/profile', 'error', 'Username and email are required');
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            $this->logging_service->logToDatabase('PROFILE_UPDATE_VALIDATION_ERROR', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'error' => 'Username must be between 3 and 50 characters',
                'validation_failed' => true,
                'username_length' => strlen($username),
                'username_value' => $username
            ]);
            $this->redirectWithFlash('/profile', 'error', 'Username must be between 3 and 50 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logging_service->logToDatabase('PROFILE_UPDATE_VALIDATION_ERROR', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'error' => 'Please enter a valid email address',
                'validation_failed' => true,
                'email_value' => $email
            ]);
            $this->redirectWithFlash('/profile', 'error', 'Please enter a valid email address');
        }

        try {
            $update_data = [
                'username' => $username,
                'email' => $email,
                'updated_at' => $this->database_service->createDate()
            ];

            $this->logging_service->logToDatabase('PROFILE_UPDATE_DATA_PREPARATION', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'update_data' => $update_data,
                'original_user_data' => [
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);

            // Check if username is already taken by another user
            $existing_user = $this->database_service->findOne('users', [
                'username' => $username,
                'id !=' => $user['id']
            ]);
            if ($existing_user) {
                $this->logging_service->logToDatabase('PROFILE_UPDATE_CONFLICT', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'error' => 'Username already exists',
                    'conflict_type' => 'username',
                    'conflicting_user_id' => $existing_user['id'],
                    'requested_username' => $username
                ]);
                $this->redirectWithFlash('/profile', 'error', 'Username already exists');
            }

            // Check if email is already taken by another user
            $existing_email = $this->database_service->findOne('users', [
                'email' => $email,
                'id !=' => $user['id']
            ]);
            if ($existing_email) {
                $this->logging_service->logToDatabase('PROFILE_UPDATE_CONFLICT', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'error' => 'Email already registered',
                    'conflict_type' => 'email',
                    'conflicting_user_id' => $existing_email['id'],
                    'requested_email' => $email
                ]);
                $this->redirectWithFlash('/profile', 'error', 'Email already registered');
            }

            // Handle password change if provided
            if (!empty($current_password)) {
                $this->logging_service->logToDatabase('PROFILE_UPDATE_PASSWORD_CHANGE_ATTEMPT', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'has_current_password' => true,
                    'has_new_password' => !empty($new_password),
                    'has_confirm_password' => !empty($confirm_password)
                ]);

                if (!password_verify($current_password, $user['password'])) {
                    $this->logging_service->logToDatabase('PROFILE_UPDATE_PASSWORD_VERIFICATION_FAILED', [
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'error' => 'Current password is incorrect',
                        'password_verification_failed' => true
                    ]);
                    $this->redirectWithFlash('/profile', 'error', 'Current password is incorrect');
                }

                if (empty($new_password)) {
                    $this->logging_service->logToDatabase('PROFILE_UPDATE_PASSWORD_VALIDATION_ERROR', [
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'error' => 'New password is required',
                        'validation_failed' => true
                    ]);
                    $this->redirectWithFlash('/profile', 'error', 'New password is required');
                }

                if (strlen($new_password) < 6) {
                    $this->logging_service->logToDatabase('PROFILE_UPDATE_PASSWORD_VALIDATION_ERROR', [
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'error' => 'New password must be at least 6 characters',
                        'validation_failed' => true,
                        'password_length' => strlen($new_password)
                    ]);
                    $this->redirectWithFlash('/profile', 'error', 'New password must be at least 6 characters');
                }

                if ($new_password !== $confirm_password) {
                    $this->logging_service->logToDatabase('PROFILE_UPDATE_PASSWORD_VALIDATION_ERROR', [
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'error' => 'New passwords do not match',
                        'validation_failed' => true,
                        'password_mismatch' => true
                    ]);
                    $this->redirectWithFlash('/profile', 'error', 'New passwords do not match');
                }

                $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                
                $this->logging_service->logToDatabase('PROFILE_UPDATE_PASSWORD_HASHED', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'password_hash_algorithm' => PASSWORD_DEFAULT,
                    'password_length' => strlen($new_password)
                ]);
            }

            $this->logging_service->logToDatabase('PROFILE_UPDATE_DATABASE_CALL', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'final_update_data' => $update_data,
                'calling_method' => 'DatabaseService::updateOne'
            ]);

            $this->database_service->updateOne('users', ['id' => $user['id']], $update_data);
            $this->logDatabaseOperation('update_profile', 'users', ['user_id' => $user['id']]);

            $this->logging_service->logToDatabase('PROFILE_UPDATE_SUCCESS', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'updated_fields' => array_keys($update_data),
                'update_timestamp' => date('Y-m-d H:i:s')
            ]);

            // Update session with new user data
            $updated_user = $this->database_service->findOne('users', ['id' => $user['id']]);
            $this->session_service->setUserSession($updated_user);

            $this->logging_service->logToDatabase('PROFILE_UPDATE_SESSION_UPDATED', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'session_updated' => true,
                'new_session_data' => [
                    'username' => $updated_user['username'],
                    'email' => $updated_user['email']
                ]
            ]);

            $this->redirectWithFlash('/profile', 'success', 'Profile updated successfully');
        } catch (Exception $e) {
            $this->logging_service->logToDatabase('PROFILE_UPDATE_EXCEPTION', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            $this->redirectWithFlash('/profile', 'error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Extract device fingerprint data from various sources
     */
    private function extractFingerprintData(): ?array
    {
        try {
            // Check if we have any fingerprint data in POST
            $has_fingerprint_data = false;
            
            // Check for individual fingerprint fields
            $fingerprint_fields = [
                'screen_resolution', 'viewport_size', 'device_pixel_ratio', 'color_depth',
                'canvas_fingerprint', 'webgl_renderer', 'hardware_cores', 'device_memory',
                'max_touch_points', 'audio_sample_rate', 'timezone', 'battery_api',
                'browser_version', 'os_version', 'connection_type', 'network_speed',
                'available_fonts', 'font_count', 'plugin_count', 'mime_type_count',
                'media_capabilities'
            ];
            
            foreach ($fingerprint_fields as $field) {
                if (isset($_POST[$field]) && !empty($_POST[$field])) {
                    $has_fingerprint_data = true;
                    break;
                }
            }
            
            if ($has_fingerprint_data) {
                // Generate a fingerprint hash if not provided
                $fingerprint_hash = $_POST['device_fingerprint'] ?? null;
                if (!$fingerprint_hash) {
                    // Create a hash from available data
                    $hash_data = [];
                    foreach ($fingerprint_fields as $field) {
                        if (isset($_POST[$field]) && !empty($_POST[$field])) {
                            $hash_data[$field] = $_POST[$field];
                        }
                    }
                    $hash_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    $fingerprint_hash = hash('sha256', json_encode($hash_data));
                }
                
                // Extract all the fingerprinting fields
                $fingerprint_data = [
                    'fingerprint_hash' => $fingerprint_hash,
                    'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'timestamp' => time(),
                    
                    // Display & Graphics
                    'screen_resolution' => $_POST['screen_resolution'] ?? null,
                    'viewport_size' => $_POST['viewport_size'] ?? null,
                    'device_pixel_ratio' => $_POST['device_pixel_ratio'] ?? null,
                    'color_depth' => $_POST['color_depth'] ?? null,
                    'canvas_fingerprint' => $_POST['canvas_fingerprint'] ?? null,
                    'webgl_renderer' => $_POST['webgl_renderer'] ?? null,
                    
                    // System & Hardware
                    'hardware_cores' => $_POST['hardware_cores'] ?? null,
                    'device_memory' => $_POST['device_memory'] ?? null,
                    'max_touch_points' => $_POST['max_touch_points'] ?? null,
                    'audio_sample_rate' => $_POST['audio_sample_rate'] ?? null,
                    'timezone' => $_POST['timezone'] ?? null,
                    'battery_api' => $_POST['battery_api'] ?? null,
                    
                    // Software & Browser
                    'browser_version' => $_POST['browser_version'] ?? null,
                    'os_version' => $_POST['os_version'] ?? null,
                    'connection_type' => $_POST['connection_type'] ?? null,
                    'network_speed' => $_POST['network_speed'] ?? null,
                    
                    // Fonts & Typography
                    'available_fonts' => $_POST['available_fonts'] ?? null,
                    'font_count' => $_POST['font_count'] ?? null,
                    
                    // Plugins & Media
                    'plugin_count' => $_POST['plugin_count'] ?? null,
                    'mime_type_count' => $_POST['mime_type_count'] ?? null,
                    'media_capabilities' => $_POST['media_capabilities'] ?? null
                ];
                
                return $fingerprint_data;
            }
            
            return null;
        } catch (Exception $e) {
            // error_log("Error extracting fingerprint data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Log user login to user_logins table
     */
    private function logUserLogin(int $user_id, ?array $fingerprint_data = null): void
    {
        try {
            $login_data = [
                'user_id' => $user_id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'success' => true,
                'login_timestamp' => date('Y-m-d H:i:s')
            ];

            // Debug logging
            error_log("=== LOG USER LOGIN DEBUG ===");
            error_log("User ID: " . $user_id);
            error_log("IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            error_log("Fingerprint data available: " . ($fingerprint_data ? 'Yes' : 'No'));

            // Generate a basic device name from user agent (more reliable)
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $login_data['device_name'] = $this->generateBasicDeviceName($user_agent);
            $login_data['fingerprint_hash'] = $fingerprint_data['fingerprint_hash'] ?? null;

            error_log("Generated basic device name: " . $login_data['device_name']);
            error_log("=== END LOG USER LOGIN DEBUG ===");

            // Insert login record
            $this->database_service->insertOne('user_logins', $login_data);

        } catch (Exception $e) {
            // Log error but don't break the login process
            error_log("Error logging user login: " . $e->getMessage());
        }
    }

    /**
     * Generate a basic device name from user agent string
     */
    private function generateBasicDeviceName(string $user_agent): string
    {
        // Extract browser info
        $browser = 'Unknown Browser';
        if (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $browser = 'Edge';
        }

        // Extract OS info
        $os = 'Unknown OS';
        if (strpos($user_agent, 'Windows NT 10.0') !== false) {
            $os = 'Windows 10/11';
        } elseif (strpos($user_agent, 'Windows NT 6.3') !== false) {
            $os = 'Windows 8.1';
        } elseif (strpos($user_agent, 'Windows NT 6.2') !== false) {
            $os = 'Windows 8';
        } elseif (strpos($user_agent, 'Windows NT 6.1') !== false) {
            $os = 'Windows 7';
        } elseif (strpos($user_agent, 'Mac OS X') !== false) {
            $os = 'macOS';
        } elseif (strpos($user_agent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($user_agent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($user_agent, 'iPhone') !== false) {
            $os = 'iOS';
        }

        // Detect device type
        $device_type = 'Desktop';
        if (strpos($user_agent, 'Mobile') !== false) {
            $device_type = 'Mobile';
        } elseif (strpos($user_agent, 'Tablet') !== false) {
            $device_type = 'Tablet';
        }

        return "{$browser} on {$os} ({$device_type})";
    }

    /**
     * Handle access token login for cross-site authentication
     */
    private function handleAccessTokenLogin(string $accessToken): void
    {
        try {
            // Find user with valid access token
            $user = $this->database_service->findOne('users', [
                'reset_token' => $accessToken
            ]);

            if (!$user) {
                $this->redirectWithFlash('/login', 'error', 'Invalid access token');
                return;
            }

            // Check if token has expired
            if (strtotime($user['reset_expires']) < time()) {
                $this->redirectWithFlash('/login', 'error', 'Access token has expired');
                return;
            }

            // Clear the access token after successful use
            $this->database_service->updateOne('users', ['id' => $user['id']], [
                'reset_token' => null,
                'reset_expires' => null,
                'updated_at' => $this->database_service->createDate()
            ]);

            // Set user session
            $this->session_service->setUserSession($user);
            $this->logDatabaseOperation('access_token_login', 'users', ['user_id' => $user['id']]);

            // Log successful login
            $this->logging_service->logToDatabase('ACCESS_TOKEN_LOGIN_SUCCESS', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            header('Location: /dashboard');
            exit;

        } catch (Exception $e) {
            $this->logging_service->logToDatabase('ACCESS_TOKEN_LOGIN_ERROR', [
                'token' => substr($accessToken, 0, 10) . '...',
                'exception_message' => $e->getMessage()
            ]);
            $this->redirectWithFlash('/login', 'error', 'Failed to login with access token');
        }
    }
} 