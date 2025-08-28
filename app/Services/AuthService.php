<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthService
{
    private DatabaseService $db;
    private string $jwtSecret;
    private int $jwtExpiry;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'default-secret-change-in-production';
        $this->jwtExpiry = (int)($_ENV['JWT_EXPIRY'] ?? 3600);
        
        // Note: Session should be started at the page level, not in the service
    }

    public function register(string $username, string $email, string $password, string $firstName, string $lastName): array
    {
        // Check if user already exists
        $existingUser = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = :username OR email = :email",
            ['username' => $username, 'email' => $email]
        );

        if ($existingUser) {
            throw new \InvalidArgumentException('Username or email already exists');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $userId = $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Generate JWT token
        $token = $this->generateJWT($userId, $username);

        // Store user data in session
        $this->setUserSession($userId, $username, $firstName, $lastName);

        return [
            'success' => true,
            'user_id' => $userId,
            'username' => $username,
            'token' => $token,
            'message' => 'User registered successfully'
        ];
    }

    public function login(string $usernameOrEmail, string $password): array
    {
        // Log the search parameters
        error_log("Login attempt - searching for user with: usernameOrEmail = '{$usernameOrEmail}'");
        
        // Find user by username OR email
        $user = $this->db->fetchOne(
            "SELECT id, username, password, first_name, last_name, default_theme, default_level, default_diagonals, default_reverse FROM users WHERE (username = :username OR email = :email) AND is_active = true",
            ['username' => $usernameOrEmail, 'email' => $usernameOrEmail]
        );
        
        // Log the search result
        if ($user) {
            error_log("User found: ID={$user['id']}, username={$user['username']}, has_default_theme=" . ($user['default_theme'] ?? 'NULL') . ", has_default_level=" . ($user['default_level'] ?? 'NULL'));
        } else {
            error_log("No user found with usernameOrEmail = '{$usernameOrEmail}'");
        }

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \InvalidArgumentException('Invalid username/email or password');
        }

        // Generate JWT token
        $token = $this->generateJWT($user['id'], $user['username']);

        // Store user data in session including preferences
        $this->setUserSession(
            $user['id'], 
            $user['username'], 
            $user['first_name'] ?? 'User', 
            $user['last_name'] ?? 'Name',
            $user['default_theme'] ?? 'animals',
            $user['default_level'] ?? 'medium',
            $user['default_diagonals'] ?? true,
            $user['default_reverse'] ?? true
        );

        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'] ?? 'User',
            'last_name' => $user['last_name'] ?? 'Name',
            'default_theme' => $user['default_theme'] ?? 'animals',
            'default_level' => $user['default_level'] ?? 'medium',
            'default_diagonals' => $user['default_diagonals'] ?? true,
            'default_reverse' => $user['default_reverse'] ?? true,
            'token' => $token,
            'message' => 'Login successful'
        ];
    }

    public function validateToken(string $token): ?array
    {
        // First check if we have valid session data
        if ($this->isUserLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'first_name' => $_SESSION['first_name'] ?? 'User',
                'last_name' => $_SESSION['last_name'] ?? 'Name'
            ];
        }

        // If no session, validate JWT token
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Check if user still exists and is active
            $user = $this->db->fetchOne(
                "SELECT id, username, first_name, last_name FROM users WHERE id = :id AND is_active = true",
                ['id' => $decoded->user_id]
            );

            if (!$user) {
                return null;
            }

            // Store user data in session for future requests
            $this->setUserSession($user['id'], $user['username'], $user['first_name'] ?? 'User', $user['last_name'] ?? 'Name');

            return [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'] ?? 'User',
                'last_name' => $user['last_name'] ?? 'Name'
            ];
        } catch (ExpiredException $e) {
            return null;
        } catch (SignatureInvalidException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateProfile(int $userId, array $data): array
    {
        $allowedFields = ['username', 'first_name', 'last_name', 'email', 'default_theme', 'default_level', 'default_diagonals', 'default_reverse'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new \InvalidArgumentException('No valid fields to update');
        }

        // Check username uniqueness if username is being updated
        if (isset($updateData['username'])) {
            $existingUser = $this->db->fetchOne(
                "SELECT id FROM users WHERE username = :username AND id != :userId",
                ['username' => $updateData['username'], 'userId' => $userId]
            );
            
            if ($existingUser) {
                throw new \InvalidArgumentException('Username already exists');
            }
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('users', $updateData, ['id' => $userId]);

        // Update session data if username was changed
        if (isset($updateData['username']) && $this->isUserLoggedIn() && $_SESSION['user_id'] == $userId) {
            $_SESSION['username'] = $updateData['username'];
        }

        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        // Verify current password
        $user = $this->db->fetchOne(
            "SELECT password FROM users WHERE id = :id",
            ['id' => $userId]
        );

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password
        $this->db->update('users', [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);

        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    }

    public function getUserProfile(int $userId): ?array
    {
        // First try to get profile from session (no database call)
        if ($this->isUserLoggedIn() && $_SESSION['user_id'] == $userId) {
            // Get only email and created_at from database (these change rarely)
            $additionalData = $this->db->fetchOne(
                "SELECT email, created_at FROM users WHERE id = :id AND is_active = true",
                ['id' => $userId]
            );
            
            if ($additionalData) {
                return array_merge([
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'first_name' => $_SESSION['first_name'] ?? 'User',
                    'last_name' => $_SESSION['last_name'] ?? 'Name',
                    'default_theme' => $_SESSION['default_theme'] ?? 'animals',
                    'default_level' => $_SESSION['default_level'] ?? 'medium',
                    'default_diagonals' => $_SESSION['default_diagonals'] ?? true,
                    'default_reverse' => $_SESSION['default_reverse'] ?? true
                ], $additionalData);
            }
        }
        
        // Fallback to database query if session data not available
         return $this->db->fetchOne(
            "SELECT id, username, email, first_name, last_name, default_theme, default_level, default_diagonals, default_reverse, created_at FROM users WHERE id = :id AND is_active = true",
            ['id' => $userId]
        );
    }

    private function generateJWT(int $userId, string $username): string
    {
        $payload = [
            'user_id' => $userId,
            'username' => $username,
            'iat' => time(),
            'exp' => time() + $this->jwtExpiry
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function logout(string $token): array
    {
        // Clear session data
        $this->clearUserSession();
        
        // In a more sophisticated system, you might want to blacklist the token
        // For now, we'll just return success as the client will discard the token
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Check if user is currently logged in via session
     */
    public function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }

    /**
     * Get current user from session (no database call)
     */
    public function getCurrentUser(): ?array
    {
        if ($this->isUserLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'first_name' => $_SESSION['first_name'] ?? 'User',
                'last_name' => $_SESSION['last_name'] ?? 'Name',
                'default_theme' => $_SESSION['default_theme'] ?? 'animals',
                'default_level' => $_SESSION['default_level'] ?? 'medium',
                'default_diagonals' => $_SESSION['default_diagonals'] ?? true,
                'default_reverse' => $_SESSION['default_reverse'] ?? true
            ];
        }
        return null;
    }

    /**
     * Set user session data
     */
    private function setUserSession(
        int $userId, 
        string $username, 
        string $firstName, 
        string $lastName,
        string $defaultTheme = 'animals',
        string $defaultLevel = 'medium',
        bool $defaultDiagonals = true,
        bool $defaultReverse = true
    ): void {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['default_theme'] = $defaultTheme;
        $_SESSION['default_level'] = $defaultLevel;
        $_SESSION['default_diagonals'] = $defaultDiagonals;
        $_SESSION['default_reverse'] = $defaultReverse;
        $_SESSION['last_activity'] = time();
    }

    /**
     * Clear user session data
     */
    private function clearUserSession(): void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);
        unset($_SESSION['default_theme']);
        unset($_SESSION['default_level']);
        unset($_SESSION['default_diagonals']);
        unset($_SESSION['default_reverse']);
        unset($_SESSION['last_activity']);
    }
}
