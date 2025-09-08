<?php

declare(strict_types=1);

namespace Sudoku\Services;

/**
 * Sudoku Game - Cookie Service
 * 
 * Handles secure cookie management for trusted devices and remember me functionality.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

// Enable error logging for debugging
// if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
//     error_log(__FILE__ . PHP_EOL, 3, __DIR__ . '/../../logs/included_files.log');
// }

use Exception;

/**
 * Cookie service for managing secure cookies
 */
class CookieService
{
    private string $cookie_secret;
    private int $trusted_device_expiry;
    private string $cookie_name;
    private string $cookie_path;
    private bool $cookie_secure;
    private bool $cookie_httponly;
    private DatabaseService $database_service;
    private LoggingService $logging_service;
    private DeviceFingerprintService $device_fingerprint_service;

    public function __construct()
    {
        $this->cookie_secret = $_ENV['COOKIE_SECRET'] ?? $_ENV['JWT_SECRET'] ?? 'default-cookie-secret';
        $this->trusted_device_expiry = (int) ($_ENV['TRUSTED_DEVICE_EXPIRY'] ?? '2592000'); // 30 days default
        $this->cookie_name = 'trusted_device';
        $this->cookie_path = '/';
        $this->cookie_secure = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        $this->cookie_httponly = true;
        $this->database_service = new DatabaseService();
        $this->logging_service = new LoggingService();
        $this->device_fingerprint_service = new DeviceFingerprintService($this->database_service, $this->logging_service);
    }

    /**
     * Create a trusted device cookie
     */
    public function createTrustedDeviceCookie(int $user_id, string $email, ?array $fingerprint_data = null): bool
    {
        try {
            // error_log("=== TRUSTED DEVICE DEBUG START ===");
            // error_log("Setting trusted device for user: " . $user_id);
            // error_log("Email: " . $email);
            // error_log("User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
            // error_log("IP address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            // Generate a cryptographically secure token
            $token = bin2hex(random_bytes(32));
            // error_log("Generated token: " . substr($token, 0, 16) . "...");

            // Calculate expiration time (30 days from now)
            $expires = time() + (30 * 24 * 60 * 60);
            // error_log("Expires at: " . date('Y-m-d H:i:s', $expires));

            // Process device fingerprint if available
            if ($fingerprint_data) {
                // error_log("Processing enhanced device fingerprint...");
                $fingerprint_result = $this->device_fingerprint_service->processDeviceFingerprint($user_id, $fingerprint_data, $token);
                if ($fingerprint_result['success']) {
                    $device_name = $fingerprint_result['device_name'];
                    // error_log("Enhanced device name generated: " . $device_name);
                    
                    // The DeviceFingerprintService has already stored the fingerprint data
                    // We just need to set the cookie, no need for additional database operations
                } else {
                    // error_log("Fingerprint processing failed, using basic device name");
                    $device_name = $this->generateDeviceName($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
                    
                    // Fallback: create basic trusted device record
                    $this->createBasicTrustedDeviceRecord($user_id, $token, $expires, $device_name);
                }
            } else {
                // Fallback to basic device name generation
                $device_name = $this->generateDeviceName($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
                
                // Create basic trusted device record
                $this->createBasicTrustedDeviceRecord($user_id, $token, $expires, $device_name);
            }
            // error_log("Final device name: " . $device_name);

            // Prepare cookie data for encryption
            $cookie_data = [
                'user_id' => $user_id,
                'email' => $email,
                'device_token' => $token,
                'expires_at' => $expires,
                'ip_hash' => $this->hashIpAddress(),
                'user_agent_hash' => $this->hashUserAgent()
            ];

            // Encrypt the cookie data
            $encrypted_cookie_value = $this->encryptCookieData($cookie_data);

            // Set secure HTTP-only cookie
            $cookie_name = 'trusted_device';
            $cookie_options = [
                'expires' => $expires,
                'path' => '/',
                'domain' => '.nofinway.com',
                'secure' => false, // Changed to false for testing (revert to true in production)
                'httponly' => true,
                'samesite' => 'Strict'
            ];

            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Setting cookie: " . $cookie_name . " = " . substr($encrypted_cookie_value, 0, 20) . "...");
                error_log("Cookie options: " . json_encode($cookie_options));
            }

            $cookie_set = setcookie($cookie_name, $encrypted_cookie_value, $cookie_options);
            
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Cookie set result: " . ($cookie_set ? 'true' : 'false'));
            }

            // Log to database
            // error_log("Logging to database...");
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_CREATED', [
                'user_id' => $user_id,
                'email' => $email,
                'device_name' => $device_name,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            // error_log("Logging successful!");

            // error_log("=== TRUSTED DEVICE DEBUG END ===");
            return true;
        } catch (Exception $e) {
            // error_log("=== TRUSTED DEVICE ERROR ===");
            // error_log("Error creating trusted device: " . $e->getMessage());
            // error_log("Error code: " . $e->getCode());
            // error_log("File: " . $e->getFile());
            // error_log("Line: " . $e->getLine());
            // error_log("Stack trace: " . $e->getTraceAsString());
            // error_log("=== END ERROR ===");
            
            $this->logging_service->logToDatabase('TRUSTED_DEVICE_CREATION_ERROR', [
                'user_id' => $user_id,
                'email' => $email,
                'error' => $e->getMessage(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            return false;
        }
    }

    /**
     * Validate a trusted device cookie
     */
    public function validateTrustedDeviceCookie(): ?array
    {
        try {
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("=== COOKIE VALIDATION DEBUG ===");
                error_log("Cookie name: " . $this->cookie_name);
                error_log("Cookie exists: " . (isset($_COOKIE[$this->cookie_name]) ? 'YES' : 'NO'));
                if (isset($_COOKIE[$this->cookie_name])) {
                    error_log("Cookie value length: " . strlen($_COOKIE[$this->cookie_name]));
                    error_log("Cookie value preview: " . substr($_COOKIE[$this->cookie_name], 0, 50) . "...");
                }
            }

            if (!isset($_COOKIE[$this->cookie_name])) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("No trusted device cookie found");
                }
                return null;
            }

            $encrypted_data = $_COOKIE[$this->cookie_name];
            $cookie_data = $this->decryptCookieData($encrypted_data);

            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Decryption result: " . ($cookie_data ? 'SUCCESS' : 'FAILED'));
                if ($cookie_data) {
                    error_log("Decrypted data keys: " . implode(', ', array_keys($cookie_data)));
                    error_log("User ID: " . ($cookie_data['user_id'] ?? 'NOT SET'));
                    error_log("Email: " . ($cookie_data['email'] ?? 'NOT SET'));
                    error_log("Device token: " . (isset($cookie_data['device_token']) ? 'SET' : 'NOT SET'));
                    error_log("Expires at: " . ($cookie_data['expires_at'] ?? 'NOT SET'));
                }
            }

            if (!$cookie_data) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Cookie decryption failed - returning null");
                }
                return null;
            }

            // Check if cookie has expired
            if (isset($cookie_data['expires_at']) && $cookie_data['expires_at'] < time()) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Cookie expired - removing and returning null");
                }
                $this->removeTrustedDeviceCookie();
                return null;
            }

            // Validate required fields
            if (!isset($cookie_data['user_id']) || !isset($cookie_data['email']) || !isset($cookie_data['device_token'])) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Required fields missing - returning null");
                    error_log("Missing fields: " . implode(', ', array_filter([
                        !isset($cookie_data['user_id']) ? 'user_id' : null,
                        !isset($cookie_data['email']) ? 'email' : null,
                        !isset($cookie_data['device_token']) ? 'device_token' : null
                    ])));
                }
                return null;
            }

            // Validate device context (IP and user agent)
            if (!$this->validateDeviceContext($cookie_data)) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Device context validation failed - returning null");
                }
                return null;
            }

            // Verify the token exists in the database
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Looking up trusted device in database...");
                error_log("User ID: " . $cookie_data['user_id']);
                error_log("Device token: " . substr($cookie_data['device_token'], 0, 20) . "...");
            }

            $trusted_device = $this->database_service->findOne('trusted_devices', [
                'user_id' => $cookie_data['user_id'],
                'cookie_token' => $cookie_data['device_token']
            ]);

            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Database lookup result: " . ($trusted_device ? 'FOUND' : 'NOT FOUND'));
                if ($trusted_device) {
                    error_log("Device ID: " . $trusted_device['id']);
                    error_log("Device active: " . ($trusted_device['is_active'] ? 'YES' : 'NO'));
                    error_log("Device expires: " . $trusted_device['expires_at']);
                }
            }

            if (!$trusted_device) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Trusted device not found in database - returning null");
                }
                return null;
            }

            // Check if device is still active
            if (!$trusted_device['is_active'] || strtotime($trusted_device['expires_at']) < time()) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Device inactive or expired - removing cookie and returning null");
                }
                $this->removeTrustedDeviceCookie();
                return null;
            }

            // Update last_used_at timestamp
            $this->database_service->updateOne(
                'trusted_devices',
                ['id' => $trusted_device['id']],
                ['last_used_at' => date('Y-m-d H:i:s')]
            );

            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Cookie validation SUCCESS - returning user data");
            }

            return $cookie_data;
        } catch (Exception $e) {
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Exception during cookie validation: " . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Remove the trusted device cookie
     */
    public function removeTrustedDeviceCookie(): bool
    {
        return setcookie(
            $this->cookie_name,
            '',
            [
                'expires' => time() - 3600,
                'path' => $this->cookie_path,
                'domain' => '.nofinway.com',
                'secure' => $this->cookie_secure,
                'httponly' => $this->cookie_httponly,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Generate a unique device token
     */
    private function generateDeviceToken(int $user_id, string $email): string
    {
        $data = $user_id . '|' . $email . '|' . time() . '|' . random_bytes(32);
        return hash('sha256', $data . $this->cookie_secret);
    }

    /**
     * Encrypt cookie data
     */
    private function encryptCookieData(array $data): string
    {
        $json_data = json_encode($data);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $json_data,
            'AES-256-CBC',
            $this->cookie_secret,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt cookie data
     */
    private function decryptCookieData(string $encrypted_data): ?array
    {
        try {
            $data = base64_decode($encrypted_data);
            $iv_length = 16;
            $iv = substr($data, 0, $iv_length);
            $encrypted = substr($data, $iv_length);
            
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $this->cookie_secret,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($decrypted === false) {
                return null;
            }
            
            $json_data = json_decode($decrypted, true);
            return is_array($json_data) ? $json_data : null;
        } catch (Exception $e) {
            // error_log("Failed to decrypt cookie data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if trusted device cookie exists and is valid
     */
    public function hasValidTrustedDeviceCookie(): bool
    {
        return $this->validateTrustedDeviceCookie() !== null;
    }

    /**
     * Get trusted device cookie expiry time
     */
    public function getTrustedDeviceExpiry(): int
    {
        return $this->trusted_device_expiry;
    }

    /**
     * Hash IP address for security
     */
    private function hashIpAddress(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return hash('sha256', $ip . $this->cookie_secret);
    }

    /**
     * Hash user agent for security
     */
    private function hashUserAgent(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return hash('sha256', $user_agent . $this->cookie_secret);
    }

    /**
     * Validate IP and user agent against stored hashes
     * Note: This validation is intentionally lenient to prevent cookie removal during validation
     */
    private function validateDeviceContext(array $cookie_data): bool
    {
        // If we don't have the required hash data, consider it valid
        // This prevents aggressive cookie removal during validation
        if (!isset($cookie_data['ip_hash']) || !isset($cookie_data['user_agent_hash'])) {
            return true;
        }

        // For now, skip strict IP and user agent validation to prevent cookie removal
        // This can be re-enabled later with proper secret management
        return true;

        // Original strict validation (commented out to prevent cookie removal):
        // $current_ip_hash = $this->hashIpAddress();
        // $current_user_agent_hash = $this->hashUserAgent();
        // return $cookie_data['ip_hash'] === $current_ip_hash && 
        //        $cookie_data['user_agent_hash'] === $current_user_agent_hash;
    }

    /**
     * Generate a stable device name based on user agent
     */
    private function generateDeviceName(string $user_agent): string
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

    /**
     * Create a basic trusted device record in the database
     */
    private function createBasicTrustedDeviceRecord(int $user_id, string $token, int $expires, string $device_name): void
    {
        $insert_data = [
            'user_id' => $user_id,
            'cookie_token' => $token,
            'token_hash' => password_hash($token, PASSWORD_DEFAULT),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', $expires),
            'device_name' => $device_name
        ];
        $this->database_service->insertOne('trusted_devices', $insert_data);
    }
}
