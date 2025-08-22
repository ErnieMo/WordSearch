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

        return [
            'success' => true,
            'user_id' => $userId,
            'username' => $username,
            'token' => $token,
            'message' => 'User registered successfully'
        ];
    }

    public function login(string $username, string $password): array
    {
        // Find user
        $user = $this->db->fetchOne(
            "SELECT id, username, password, first_name, last_name FROM users WHERE username = :username AND is_active = true",
            ['username' => $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \InvalidArgumentException('Invalid username or password');
        }

        // Generate JWT token
        $token = $this->generateJWT($user['id'], $user['username']);

        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'token' => $token,
            'message' => 'Login successful'
        ];
    }

    public function validateToken(string $token): ?array
    {
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

            return [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
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
        $allowedFields = ['first_name', 'last_name', 'email'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new \InvalidArgumentException('No valid fields to update');
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('users', $updateData, ['id' => $userId]);

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
        return $this->db->fetchOne(
            "SELECT id, username, email, first_name, last_name, created_at FROM users WHERE id = :id AND is_active = true",
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
        // In a more sophisticated system, you might want to blacklist the token
        // For now, we'll just return success as the client will discard the token
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
}
