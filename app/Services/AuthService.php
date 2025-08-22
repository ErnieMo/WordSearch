<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private DatabaseService $db;
    private array $config;

    public function __construct(DatabaseService $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function register(string $firstName, string $lastName, string $username, string $email, string $password): array
    {
        // Check if user already exists
        $existingUser = $this->db->findOne('users', 'username = :username OR email = :email', [
            'username' => $username,
            'email' => $email
        ]);

        if ($existingUser) {
            throw new Exception('Username or email already exists');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Create user
        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'is_active' => true,
            'email_verified' => false
        ];
        
        // Log the data being inserted for debugging
        error_log("Inserting user data: " . json_encode($userData));
        
        $userId = $this->db->insert('users', $userData);

        if (!$userId) {
            throw new Exception('Failed to create user');
        }

        // Generate JWT token
        try {
            $token = $this->generateJWT($userId, $username, $email);
            error_log("JWT token generated successfully for user: " . $username);
        } catch (Exception $e) {
            error_log("JWT generation failed for user: " . $username . " - Error: " . $e->getMessage());
            throw $e;
        }

        return [
            'success' => true,
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email,
            'token' => $token
        ];
    }

    public function login(string $username, string $password): array
    {
        // Log login attempt
        error_log("Login attempt for username: " . $username);
        
        // Find user by username or email
        $user = $this->db->findOne('users', 'username = :username OR email = :username', [
            'username' => $username
        ]);

        if (!$user) {
            error_log("User not found for username: " . $username);
            throw new Exception('Invalid credentials');
        }

        if (!$user['is_active']) {
            error_log("User account deactivated for username: " . $username);
            throw new Exception('Account is deactivated');
        }

        // Log user found (without sensitive data)
        error_log("User found: ID=" . $user['id'] . ", Username=" . $user['username'] . ", Email=" . $user['email']);

        // Verify password
        if (!password_verify($password, $user['password'])) {
            error_log("Password verification failed for username: " . $username);
            throw new Exception('Invalid credentials');
        }

        error_log("Password verified successfully for username: " . $username);

        // Generate JWT token
        $token = $this->generateJWT($user['id'], $user['username'], $user['email']);

        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'token' => $token
        ];
    }

    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->config['JWT_SECRET'], 'HS256'));
            
            // Check if user still exists and is active
            $user = $this->db->findOne('users', 'id = :id AND is_active = true', [
                'id' => $decoded->user_id
            ]);

            if (!$user) {
                return null;
            }

            return [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];

        } catch (Exception $e) {
            return null;
        }
    }

    public function refreshToken(string $token): ?string
    {
        $userData = $this->verifyToken($token);
        if (!$userData) {
            return null;
        }

        return $this->generateJWT($userData['user_id'], $userData['username'], $userData['email']);
    }

    private function generateJWT(int $userId, string $username, string $email): string
    {
        try {
            $payload = [
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'iat' => time(),
                'exp' => time() + ($this->config['JWT_EXPIRY'] ?? 3600)
            ];

            error_log("Generating JWT with payload: " . json_encode($payload));
            error_log("JWT Secret length: " . strlen($this->config['JWT_SECRET']));

            $token = JWT::encode($payload, $this->config['JWT_SECRET'], 'HS256');
            error_log("JWT token generated successfully, length: " . strlen($token));
            
            return $token;
        } catch (Exception $e) {
            error_log("JWT generation error: " . $e->getMessage());
            error_log("JWT generation error trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->db->findOne('users', 'id = :id', ['id' => $userId]);
        if (!$user) {
            throw new Exception('User not found');
        }

        if (!password_verify($currentPassword, $user['password'])) {
            throw new Exception('Current password is incorrect');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $this->db->update('users', 
            ['password' => $hashedPassword], 
            'id = :id', 
            ['id' => $userId]
        );

        return true;
    }

    public function resetPassword(string $email): bool
    {
        $user = $this->db->findOne('users', 'email = :email AND is_active = true', ['email' => $email]);
        if (!$user) {
            throw new Exception('User not found');
        }

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $resetExpires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $this->db->update('users', 
            ['reset_token' => $resetToken, 'reset_expires' => $resetExpires], 
            'id = :id', 
            ['id' => $user['id']]
        );

        // TODO: Send email with reset link
        // For now, just return success
        return true;
    }

    public function getUserById(int $userId): ?array
    {
        $user = $this->db->findOne('users', 'id = :id', ['id' => $userId]);
        if ($user) {
            // Return only safe user data (exclude password)
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'created_at' => $user['created_at'],
                'is_active' => $user['is_active']
            ];
        }
        return null;
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $allowedFields = ['first_name', 'last_name', 'username', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return false;
        }

        $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
        return true;
    }
}
