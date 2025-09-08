<?php
declare(strict_types=1);

/**
 * Transfer Token Service for Cross-Site Authentication
 * 
 * Generates and validates secure tokens for transferring user sessions
 * between different applications using the same database.
 * 
 * @author WordSearch Game Team
 * @last_modified 2024-01-01
 */

// Log file inclusion for debugging
error_log(__FILE__ . PHP_EOL, 3, __DIR__ . '/../../log/included_files.log');

use App\Services\DatabaseService;

class TransferTokenService
{
    private DatabaseService $db;
    private string $secretKey;
    private int $tokenLifetime;

    public function __construct(DatabaseService $db, string $secretKey = null, int $tokenLifetime = 300)
    {
        $this->db = $db;
        $this->secretKey = $secretKey ?? $_ENV['JWT_SECRET'] ?? 'default-secret-key-change-in-production';
        $this->tokenLifetime = $tokenLifetime; // 5 minutes default
    }

    /**
     * Generate a secure transfer token for a user
     */
    public function generateTransferToken(int $userId, string $sourceApp = 'wordsearch'): string
    {
        try {
            // Get user data from database
            $user = $this->db->fetchOne(
                'SELECT id, username, email, first_name, last_name, password, is_active, 
                        email_verified, reset_token, reset_expires, default_theme, 
                        default_level, isadmin, default_diagonals, default_reverse, 
                        created_at, updated_at 
                 FROM users WHERE id = :id AND is_active = true',
                ['id' => $userId]
            );

            if (!$user) {
                throw new RuntimeException('User not found or inactive');
            }

            // Create token data
            $tokenData = [
                'user_id' => $userId,
                'username' => $user['username'],
                'email' => $user['email'],
                'source_app' => $sourceApp,
                'expires_at' => time() + $this->tokenLifetime,
                'created_at' => time()
            ];

            // Generate secure token
            $token = $this->createSecureToken($tokenData);

            // Store token in database for validation
            $this->db->execute(
                'INSERT INTO transfer_tokens (token, user_id, source_app, expires_at, created_at) 
                 VALUES (:token, :user_id, :source_app, :expires_at, NOW())',
                [
                    'token' => $token,
                    'user_id' => $userId,
                    'source_app' => $sourceApp,
                    'expires_at' => date('Y-m-d H:i:s', $tokenData['expires_at'])
                ]
            );

            return $token;

        } catch (Exception $e) {
            error_log("Token generation error: " . $e->getMessage());
            throw new RuntimeException('Failed to generate transfer token');
        }
    }

    /**
     * Validate a transfer token and return user data
     */
    public function validateTransferToken(string $token): ?array
    {
        try {
            // Check if token exists in database and is not expired
            $tokenRecord = $this->db->fetchOne(
                'SELECT * FROM transfer_tokens 
                 WHERE token = :token AND expires_at > NOW()',
                ['token' => $token]
            );

            if (!$tokenRecord) {
                return null;
            }

            // Verify token signature
            if (!$this->verifyTokenSignature($token)) {
                return null;
            }

            // Get fresh user data
            $user = $this->db->fetchOne(
                'SELECT id, username, email, first_name, last_name, password, is_active, 
                        email_verified, reset_token, reset_expires, default_theme, 
                        default_level, isadmin, default_diagonals, default_reverse, 
                        created_at, updated_at 
                 FROM users WHERE id = :id AND is_active = true',
                ['id' => $tokenRecord['user_id']]
            );

            if (!$user) {
                return null;
            }

            // Mark token as used
            $this->db->execute(
                'UPDATE transfer_tokens SET used_at = NOW() WHERE token = :token',
                ['token' => $token]
            );

            return $user;

        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a secure token with signature
     */
    private function createSecureToken(array $data): string
    {
        // Create payload
        $payload = base64_encode(json_encode($data));
        
        // Create signature
        $signature = hash_hmac('sha256', $payload, $this->secretKey);
        
        // Combine payload and signature
        return $payload . '.' . $signature;
    }

    /**
     * Verify token signature
     */
    private function verifyTokenSignature(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return false;
        }

        [$payload, $signature] = $parts;
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $payload, $this->secretKey);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Create transfer URL
     */
    public function createTransferUrl(string $token, string $targetApp = 'tileslider'): string
    {
        $baseUrl = match($targetApp) {
            'tileslider' => 'https://tileslider.nofinway.com',
            'sudoku' => 'https://sudoku.nofinway.com',
            'wordsearch' => 'https://wordsearch.nofinway.com',
            default => 'https://tileslider.nofinway.com'
        };
            
        return $baseUrl . '/transfer-login?token=' . urlencode($token);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        try {
            $this->db->execute(
                'DELETE FROM transfer_tokens WHERE expires_at < NOW()'
            );
            
            return $this->db->rowCount();
        } catch (Exception $e) {
            error_log("Token cleanup error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get token statistics
     */
    public function getTokenStats(): array
    {
        try {
            $stats = $this->db->fetchOne(
                'SELECT 
                    COUNT(*) as total_tokens,
                    COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_tokens,
                    COUNT(CASE WHEN used_at IS NOT NULL THEN 1 END) as used_tokens
                 FROM transfer_tokens'
            );

            return $stats ?: ['total_tokens' => 0, 'active_tokens' => 0, 'used_tokens' => 0];
        } catch (Exception $e) {
            error_log("Token stats error: " . $e->getMessage());
            return ['total_tokens' => 0, 'active_tokens' => 0, 'used_tokens' => 0];
        }
    }
}
