<?php

declare(strict_types=1);

namespace Sudoku\Services;

/**
 * Sudoku Game - Session Service
 * 
 * Handles user sessions, JWT tokens, and authentication state management.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

// Enable error logging for debugging
if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
// error_log("\n\n" . __FILE__ . PHP_EOL, 3, __DIR__ . '/../../../../Logs/included_files.log');
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * Session service for managing user sessions and JWT tokens
 */
class SessionService
{
    private string $jwt_secret;
    private int $jwt_expiry;
    private DatabaseService $database_service;
    private LoggingService $logging_service;

    public function __construct(DatabaseService $database_service, LoggingService $logging_service)
    {
        $this->jwt_secret = $_ENV['JWT_SECRET'] ?? 'default-jwt-secret-change-in-production';
        $this->jwt_expiry = (int) ($_ENV['JWT_EXPIRY'] ?? '3600');
        $this->database_service = $database_service;
        $this->logging_service = $logging_service;
    }

    /**
     * Create a JWT token for a user
     */
    public function createToken(array $user_data): string
    {
        $payload = [
            'user_id' => $user_data['id'] ?? $user_data['_id'],
            'email' => $user_data['email'],
            'username' => $user_data['username'],
            'iat' => time(),
            'exp' => time() + $this->jwt_expiry
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    /**
     * Decode and validate a JWT token
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get current user from session or token
     */
    public function getCurrentUser(): ?array
    {
        // error_log("\n=== GET CURRENT USER START ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        // Check session first
        if (isset($_SESSION['user'])) {
            // error_log("User found in session: " . json_encode($_SESSION['user']));
            // error_log("=== GET CURRENT USER END ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
            return $_SESSION['user'];
        }

        // Check for trusted device cookie
        if (isset($_COOKIE['trusted_device'])) {
            $user_data = $this->validateTrustedDeviceCookie($_COOKIE['trusted_device']);
            if ($user_data) {
                // error_log("User authenticated via trusted device cookie: " . json_encode($user_data));
                $_SESSION['user'] = $user_data;
                $_SESSION['authenticated'] = true;
                $_SESSION['login_time'] = time();
                // error_log("=== GET CURRENT USER END ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
                return $user_data;
            }
        }

        // error_log("No user in session, checking JWT token");

        // Check for JWT token in Authorization header
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        // error_log("Authorization header: " . $auth_header, 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        if (str_starts_with($auth_header, 'Bearer ')) {
            $token = substr($auth_header, 7);
            // error_log("JWT token found, attempting decode");
            $user_data = $this->decodeToken($token);
            
            if ($user_data) {
                // error_log("JWT token decoded successfully: " . json_encode($user_data));
                $_SESSION['user'] = $user_data;
                // error_log("=== GET CURRENT USER END ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
                return $user_data;
            } else {
                // error_log("JWT token decode failed", 3, '/var/www/html/Logs/wordsearch_debug.log');
            }
        } else {
            // error_log("No JWT token found in headers", 3, '/var/www/html/Logs/wordsearch_debug.log');
        }

        // error_log("No user found", 3, '/var/www/html/Logs/wordsearch_debug.log');
        // error_log("=== GET CURRENT USER END ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        return null;
    }

    /**
     * Validate trusted device cookie and return user data if valid
     */
    private function validateTrustedDeviceCookie(string $cookie_value): ?array
    {
        try {
            // error_log("=== VALIDATE TRUSTED DEVICE DEBUG START ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
            // error_log("Cookie value: " . substr($cookie_value, 0, 30) . "...");
            
            // Parse cookie value (format: user_id:token)
            $parts = explode(':', $cookie_value, 2);
            // error_log("Cookie parts count: " . count($parts));
            
            if (count($parts) !== 2) {
                // error_log("Invalid cookie format - expected 2 parts, got " . count($parts));
                return null;
            }

            $user_id = $parts[0];
            $token = $parts[1];
            // error_log("Parsed user_id: " . $user_id, 3, '/var/www/html/Logs/wordsearch_debug.log');
            // error_log("Parsed token: " . substr($token, 0, 16) . "...");

            // Validate user ID
            if (!is_numeric($user_id) || $user_id <= 0) {
                // error_log("Invalid user_id: " . $user_id, 3, '/var/www/html/Logs/wordsearch_debug.log');
                return null;
            }

            // Get trusted device record from database
            // error_log("Looking for trusted device record...", 3, '/var/www/html/Logs/wordsearch_debug.log');
            $trusted_device = $this->database_service->findOne('trusted_devices', [
                'user_id' => $user_id,
                'expires_at' => ['>' => time()]
            ]);

            if (!$trusted_device) {
                // error_log("No trusted device record found for user_id: " . $user_id, 3, '/var/www/html/Logs/wordsearch_debug.log');
                return null;
            }
            
            // error_log("Trusted device record found: " . json_encode($trusted_device));

            // Verify token hash
            // error_log("Verifying token...", 3, '/var/www/html/Logs/wordsearch_debug.log');
            $token_verified = password_verify($token, $trusted_device['token_hash']);
            // error_log("Token verification result: " . ($token_verified ? 'true' : 'false'));
            
            if (!$token_verified) {
                // error_log("Token verification failed", 3, '/var/www/html/Logs/wordsearch_debug.log');
                return null;
            }

            // Check if user still exists and is active
            // error_log("Getting user data...", 3, '/var/www/html/Logs/wordsearch_debug.log');
            $user = $this->database_service->findOne('users', ['id' => $user_id]);
            if (!$user) {
                // error_log("User not found for user_id: " . $user_id, 3, '/var/www/html/Logs/wordsearch_debug.log');
                return null;
            }
            
            // error_log("User found: " . json_encode(array_keys($user)));

            // Log successful trusted device authentication
            // error_log("Logging successful authentication...", 3, '/var/www/html/Logs/wordsearch_debug.log');
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_AUTH_SUCCESS', [
                'user_id' => $user_id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            // error_log("=== VALIDATE TRUSTED DEVICE DEBUG END ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
            return $user;

        } catch (Exception $e) {
            // error_log("=== VALIDATE TRUSTED DEVICE ERROR ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
            // error_log("Error validating trusted device: " . $e->getMessage());
            // error_log("Error code: " . $e->getCode());
            // error_log("File: " . $e->getFile());
            // error_log("Line: " . $e->getLine());
            // error_log("Stack trace: " . $e->getTraceAsString());
            // error_log("=== END ERROR ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
            
            // Log error but don't expose details
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_AUTH_ERROR', [
                'error' => 'Cookie validation failed',
                'exception_message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        // error_log("=== SESSION SERVICE - CHECKING AUTHENTICATION ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
        // error_log("Session ID: " . (session_id() ?? 'none'));
        // error_log("Session data: " . json_encode($_SESSION ?? []));
        
        // Check for user data in session - check for both 'id' and 'user_id' for compatibility
        $is_authenticated = isset($_SESSION['user']) && 
                           (isset($_SESSION['user']['id']) || isset($_SESSION['user']['user_id'])) && 
                           (!empty($_SESSION['user']['id']) || !empty($_SESSION['user']['user_id']));
        
        // error_log("Is authenticated from session: " . ($is_authenticated ? 'true' : 'false'));
        
        if ($is_authenticated) {
            // error_log("User ID: " . ($_SESSION['user']['id'] ?? $_SESSION['user']['user_id']));
            // error_log("Username: " . ($_SESSION['user']['username'] ?? 'not set'));
            // error_log("Email: " . ($_SESSION['user']['email'] ?? 'not set'));
        } else {
            // Try to auto-login using trusted device cookie
            // error_log("No session found, checking for trusted device cookie...");
            if (isset($_COOKIE['trusted_device'])) {
                // error_log("Trusted device cookie found, attempting auto-login...");
                $user_data = $this->validateTrustedDeviceCookie($_COOKIE['trusted_device']);
                if ($user_data) {
                    // error_log("Auto-login successful via trusted device!", 3, '/var/www/html/Logs/wordsearch_debug.log');
                    $_SESSION['user'] = $user_data;
                    $_SESSION['authenticated'] = true;
                    $_SESSION['login_time'] = time();
                    $is_authenticated = true;
                } else {
                    // error_log("Auto-login failed via trusted device", 3, '/var/www/html/Logs/wordsearch_debug.log');
                }
            } else {
                // error_log("No trusted device cookie found", 3, '/var/www/html/Logs/wordsearch_debug.log');
            }
        }
        
        // error_log("Final authentication result: " . ($is_authenticated ? 'true' : 'false'));
        // error_log("=== AUTHENTICATION CHECK COMPLETE ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
        return $is_authenticated;
    }

    /**
     * Get current user ID
     */
    public function getCurrentUserId(): ?string
    {
        $user = $this->getCurrentUser();
        return $user ? ($user['id'] ?? $user['user_id']) : null;
    }

    /**
     * Set user session
     */
    public function setUserSession(array $user_data): void
    {
        $_SESSION['user'] = $user_data;
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Clear user session
     */
    public function clearSession(): void
    {
        // // Clear trusted device cookie if it exists
        // if (isset($_COOKIE['trusted_device'])) {
        //     $this->clearTrustedDeviceCookie($_COOKIE['trusted_device']);
        // }
        // Note: We do NOT clear the trusted device cookie on logout
        // This allows users to stay logged in across subdomains even after logout
        // The trusted device cookie will persist until it expires (30 days) or is manually cleared
        
        session_destroy();
        session_start();
    }

    /**
     * Clear trusted device cookie (but preserve database record)
     */
    private function clearTrustedDeviceCookie(string $cookie_value): void
    {
        try {
            // Parse cookie value to get user ID
            $parts = explode(':', $cookie_value, 2);
            if (count($parts) === 2 && is_numeric($parts[0])) {
                $user_id = $parts[0];
                
                // DO NOT delete trusted device record from database
                // Trusted devices should persist until they expire
                // Only clear the cookie
                
                // Log that cookie was cleared (but record preserved)
                $this->logging_service->logToDatabase('TRUSTED_DEVICE_COOKIE_CLEARED', [
                    'user_id' => $user_id,
                    'reason' => 'User logout - cookie cleared, record preserved'
                ]);
            }
        } catch (Exception $e) {
            // Log error but don't expose details
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_COOKIE_CLEAR_ERROR', [
                'error' => 'Failed to clear trusted device cookie',
                'exception_message' => $e->getMessage()
            ]);
        }
        
        // Clear the cookie
        setcookie(
            'trusted_device',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '.nofinway.com',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Set a session variable
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && isset($user['isadmin']) && $user['isadmin'] === true;
    }

    /**
     * Remove a session variable
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Flash a message to the next request
     */
    public function flash(string $key, string $message): void
    {
    // error_log("=== SESSION SERVICE - FLASH MESSAGE ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    // error_log("Key: " . $key . "\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    // error_log("Message: " . $message . "\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        $_SESSION['flash'][$key] = $message;
    // error_log("Flash message stored in session\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    // error_log("=== FLASH MESSAGE STORED ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    }

    /**
     * Get a flash message
     */
    public function getFlash(string $key): ?string
    {
    // error_log("=== SESSION SERVICE - GET FLASH MESSAGE ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    // error_log("Key: " . $key . "\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        $message = $_SESSION['flash'][$key] ?? null;
    // error_log("Message found: " . ($message ? 'yes' : 'no') . "\n");
        if ($message) {
        // error_log("Message content: " . $message . "\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        }
        
        // Remove the flash message after retrieving it
        unset($_SESSION['flash'][$key]);
    // error_log("Flash message removed from session\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    // error_log("=== FLASH MESSAGE RETRIEVED ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        return $message;
    }

    /**
     * Get all flash messages
     */
    public function getAllFlash(): array
    {
    // error_log("=== SESSION SERVICE - GET ALL FLASH MESSAGES ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        $flash_messages = $_SESSION['flash'] ?? [];
    // error_log("Flash messages count: " . count($flash_messages) . "\n");
    // error_log("Flash messages: " . json_encode($flash_messages) . "\n");
        
        // Clear all flash messages
        unset($_SESSION['flash']);
    // error_log("All flash messages cleared from session\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
    // error_log("=== ALL FLASH MESSAGES RETRIEVED ===\n", 3, '/var/www/html/Logs/wordsearch_debug.log');
        
        return $flash_messages;
    }

    /**
     * Generate a stable device name based on user agent
     */
    public function generateDeviceName(string $user_agent): string
    {
        $browser = $this->detectBrowser($user_agent);
        $os = $this->detectOS($user_agent);
        $device_type = $this->detectDeviceType($user_agent);
        
        return "{$browser} on {$os} ({$device_type})";
    }

    /**
     * Detect browser from user agent
     */
    private function detectBrowser(string $user_agent): string
    {
        $user_agent = strtolower($user_agent);
        
        if (strpos($user_agent, 'chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($user_agent, 'firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($user_agent, 'safari') !== false && strpos($user_agent, 'chrome') === false) {
            return 'Safari';
        } elseif (strpos($user_agent, 'edge') !== false) {
            return 'Edge';
        } elseif (strpos($user_agent, 'opera') !== false) {
            return 'Opera';
        } else {
            return 'Unknown Browser';
        }
    }

    /**
     * Detect operating system from user agent
     */
    private function detectOS(string $user_agent): string
    {
        $user_agent = strtolower($user_agent);
        
        if (strpos($user_agent, 'windows') !== false) {
            return 'Windows';
        } elseif (strpos($user_agent, 'mac') !== false) {
            return 'macOS';
        } elseif (strpos($user_agent, 'linux') !== false) {
            return 'Linux';
        } elseif (strpos($user_agent, 'android') !== false) {
            return 'Android';
        } elseif (strpos($user_agent, 'ios') !== false || strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false) {
            return 'iOS';
        } else {
            return 'Unknown OS';
        }
    }

    /**
     * Detect device type from user agent
     */
    private function detectDeviceType(string $user_agent): string
    {
        $user_agent = strtolower($user_agent);
        
        if (strpos($user_agent, 'mobile') !== false || strpos($user_agent, 'android') !== false || strpos($user_agent, 'iphone') !== false) {
            return 'Mobile';
        } elseif (strpos($user_agent, 'tablet') !== false || strpos($user_agent, 'ipad') !== false) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
} 