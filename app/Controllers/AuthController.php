<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\DatabaseService;

class AuthController
{
    private AuthService $authService;
    private array $config;

    public function __construct(DatabaseService $db, array $config)
    {
        $this->authService = new AuthService($db, $config);
        $this->config = $config;
    }

    public function register(): string
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new \Exception('Invalid input data');
            }

            $firstName = $input['first_name'] ?? '';
            $lastName = $input['last_name'] ?? '';
            $username = $input['username'] ?? '';
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password)) {
                throw new \Exception('First name, last name, username, email, and password are required');
            }

            if (strlen($password) < 6) {
                throw new \Exception('Password must be at least 6 characters long');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format');
            }

            error_log("Registration attempt for: " . $username . " (" . $email . ")");

            $result = $this->authService->register($firstName, $lastName, $username, $email, $password);

            error_log("Registration successful for: " . $username . ", User ID: " . $result['user_id']);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user_id' => $result['user_id'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'email' => $email,
                    'token' => $result['token']
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Registration failed for: " . ($input['username'] ?? 'unknown') . " - Error: " . $e->getMessage());
            
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function login(): string
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new \Exception('Invalid input data');
            }

            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($username) || empty($password)) {
                throw new \Exception('Username and password are required');
            }

            $result = $this->authService->login($username, $password);

            // Set JWT token in cookie
            $this->setAuthCookie($result['token']);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function logout(): string
    {
        // Clear auth cookie
        $this->clearAuthCookie();

        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    public function profile(): string
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                throw new \Exception('Authentication required');
            }

            $userData = $this->authService->verifyToken($token);
            if (!$userData) {
                throw new \Exception('Invalid or expired token');
            }

            $user = $this->authService->getUserById($userData['user_id']);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'data' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'created_at' => $user['created_at']
                ]
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateProfile(): string
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                throw new \Exception('Authentication required');
            }

            $userData = $this->authService->verifyToken($token);
            if (!$userData) {
                throw new \Exception('Invalid or expired token');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new \Exception('Invalid input data');
            }

            $this->authService->updateProfile($userData['user_id'], $input);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function changePassword(): string
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                throw new \Exception('Authentication required');
            }

            $userData = $this->authService->verifyToken($token);
            if (!$userData) {
                throw new \Exception('Invalid or expired token');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new \Exception('Invalid input data');
            }

            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword)) {
                throw new \Exception('Current and new passwords are required');
            }

            if (strlen($newPassword) < 6) {
                throw new \Exception('New password must be at least 6 characters long');
            }

            $this->authService->changePassword($userData['user_id'], $currentPassword, $newPassword);

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getAuthToken(): ?string
    {
        // Check Authorization header first
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        // Check cookie
        return $_COOKIE['auth_token'] ?? null;
    }

    private function setAuthCookie(string $token): void
    {
        $secure = $this->config['SESSION_SECURE'] ?? false;
        $httpOnly = $this->config['SESSION_HTTP_ONLY'] ?? true;
        $sameSite = $this->config['SESSION_SAME_SITE'] ?? 'Strict';

        setcookie('auth_token', $token, [
            'expires' => time() + ($this->config['JWT_EXPIRY'] ?? 3600),
            'path' => '/',
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite
        ]);
    }

    private function clearAuthCookie(): void
    {
        setcookie('auth_token', '', [
            'expires' => time() - 3600,
            'path' => '/'
        ]);
    }
}
