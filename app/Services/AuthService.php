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

    public function register(string $username, string $email, string $password): array
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
        $userId = $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'is_active' => true,
            'email_verified' => false
        ]);

        if (!$userId) {
            throw new Exception('Failed to create user');
        }

        // Generate JWT token
        $token = $this->generateJWT($userId, $username, $email);

        return [
            'success' => true,
            'user_id' => $userId,
            'username' => $username,
            'email' => $email,
            'token' => $token
        ];
    }

    public function login(string $username, string $password): array
    {
        // Find user by username or email
        $user = $this->db->findOne('users', 'username = :username OR email = :username', [
            'username' => $username
        ]);

        if (!$user) {
            throw new Exception('Invalid credentials');
        }

        if (!$user['is_active']) {
            throw new Exception('Account is deactivated');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid credentials');
        }

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
        $payload = [
            'user_id' => $userId,
            'username' => $username,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + ($this->config['JWT_EXPIRY'] ?? 3600)
        ];

        return JWT::encode($payload, $this->config['JWT_SECRET'], 'HS256');
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
        return $this->db->findOne('users', 'id = :id', ['id' => $userId]);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $allowedFields = ['username', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return false;
        }

        $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
        return true;
    }
}
