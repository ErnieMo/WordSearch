<?php
/**
 * Enhanced Device Fingerprinting Service
 * Processes and validates device fingerprinting data for improved device identification
 * 
 * @author Sudoku App
 * @version 1.0.0
 * @last_modified 2024-01-01
 */

declare(strict_types=1);

namespace Sudoku\Services;

// error_log(__FILE__ . PHP_EOL, 3, __DIR__ . '/../../logs/included_files.log');

use Exception;
use PDO;

class DeviceFingerprintService
{
    private DatabaseService $database_service;
    private LoggingService $logging_service;

    public function __construct(DatabaseService $database_service, LoggingService $logging_service)
    {
        $this->database_service = $database_service;
        $this->logging_service = $logging_service;
    }

    /**
     * Process device fingerprint data and store in database
     */
    public function processDeviceFingerprint(int $user_id, array $fingerprint_data, ?string $token = null): array
    {
        try {
            // Debug logging (only in development) - Reduced for Nginx buffer limits
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("=== DEVICE FINGERPRINT DEBUG ===");
                error_log("User ID: " . $user_id . " | Token: " . ($token ? 'Yes' : 'No'));
            }
            
            // Extract and validate fingerprint data
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("About to extract fingerprint data...");
            }
            $processed_data = $this->extractFingerprintData($fingerprint_data);
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Data processed successfully");
            }
            
            // Generate enhanced device name
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("About to generate device name...");
            }
            $device_name = $this->generateEnhancedDeviceName($processed_data);
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Generated device name: " . $device_name);
            }
            
            // Store fingerprint data in database
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("About to store fingerprint data...");
            }
            $this->storeFingerprintData($user_id, $processed_data, $token);
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Fingerprint data stored successfully");
            }
            
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Returning success result");
            }
            
            return [
                'success' => true,
                'device_name' => $device_name,
                'fingerprint_hash' => $processed_data['fingerprint_hash'] ?? 'unknown'
            ];
        } catch (Exception $e) {
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("=== DEVICE FINGERPRINT ERROR ===");
                error_log("Error processing device fingerprint: " . $e->getMessage());
                error_log("Exception file: " . $e->getFile());
                error_log("Exception line: " . $e->getLine());
                error_log("Exception trace: " . $e->getTraceAsString());
            }
            
            $this->logging_service->logToDatabase('DEVICE_FINGERPRINT_ERROR', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
                'fingerprint_data' => json_encode($fingerprint_data)
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to process device fingerprint: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract and validate fingerprint data from client
     */
    private function extractFingerprintData(array $fingerprint_data): array
    {
        $processed = [];
        
        // Basic device info
        $processed['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $processed['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Screen information
        $processed['screen_resolution'] = $this->extractScreenResolution($fingerprint_data);
        $processed['viewport_size'] = $this->extractViewportSize($fingerprint_data);
        $processed['device_pixel_ratio'] = $this->extractDevicePixelRatio($fingerprint_data);
        $processed['color_depth'] = $this->extractColorDepth($fingerprint_data);
        
        // Browser capabilities
        $processed['hardware_cores'] = $this->extractHardwareCores($fingerprint_data);
        $processed['max_touch_points'] = $this->extractMaxTouchPoints($fingerprint_data);
        $processed['device_memory'] = $this->extractDeviceMemory($fingerprint_data);
        
        // Time and locale
        $processed['timezone'] = $this->extractTimezone($fingerprint_data);
        
        // Advanced fingerprinting
        $processed['canvas_fingerprint'] = $this->extractCanvasFingerprint($fingerprint_data);
        $processed['webgl_renderer'] = $this->extractWebGLRenderer($fingerprint_data);
        $processed['audio_sample_rate'] = $this->extractAudioSampleRate($fingerprint_data);
        
        // Browser and OS version
        $processed['browser_version'] = $this->extractBrowserVersion($fingerprint_data);
        $processed['os_version'] = $this->extractOSVersion($fingerprint_data);
        
        // New fingerprinting fields
        $processed['available_fonts'] = $this->extractAvailableFonts($fingerprint_data);
        $processed['font_count'] = $this->extractFontCount($fingerprint_data);
        $processed['plugin_count'] = $this->extractPluginCount($fingerprint_data);
        $processed['mime_type_count'] = $this->extractMimeTypeCount($fingerprint_data);
        $processed['media_capabilities'] = $this->extractMediaCapabilities($fingerprint_data);
        $processed['battery_api'] = $this->extractBatteryAPI($fingerprint_data);
        $processed['connection_type'] = $this->extractConnectionType($fingerprint_data);
        $processed['network_speed'] = $this->extractNetworkSpeed($fingerprint_data);
        
        // Generate fingerprint hash
        $processed['fingerprint_hash'] = $this->generateFingerprintHash($processed);
        
        return $processed;
    }

    /**
     * Extract screen resolution from fingerprint data
     */
    private function extractScreenResolution(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['screen_resolution']) && !empty($fingerprint_data['screen_resolution'])) {
            return $fingerprint_data['screen_resolution'];
        }
        return 'unknown';
    }

    /**
     * Extract viewport size from fingerprint data
     */
    private function extractViewportSize(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['viewport_size']) && !empty($fingerprint_data['viewport_size'])) {
            return $fingerprint_data['viewport_size'];
        }
        return 'unknown';
    }

    /**
     * Extract device pixel ratio from fingerprint data
     */
    private function extractDevicePixelRatio(array $fingerprint_data): ?float
    {
        if (isset($fingerprint_data['device_pixel_ratio']) && !empty($fingerprint_data['device_pixel_ratio']) && is_numeric($fingerprint_data['device_pixel_ratio'])) {
            return (float) $fingerprint_data['device_pixel_ratio'];
        }
        return null;
    }

    /**
     * Extract color depth from fingerprint data
     */
    private function extractColorDepth(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['color_depth']) && !empty($fingerprint_data['color_depth']) && is_numeric($fingerprint_data['color_depth'])) {
            return (int) $fingerprint_data['color_depth'];
        }
        return null;
    }

    /**
     * Extract hardware cores from fingerprint data
     */
    private function extractHardwareCores(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['hardware_cores']) && !empty($fingerprint_data['hardware_cores']) && is_numeric($fingerprint_data['hardware_cores'])) {
            return (int) $fingerprint_data['hardware_cores'];
        }
        return null;
    }

    /**
     * Extract max touch points from fingerprint data
     */
    private function extractMaxTouchPoints(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['max_touch_points']) && !empty($fingerprint_data['max_touch_points']) && is_numeric($fingerprint_data['max_touch_points'])) {
            return (int) $fingerprint_data['max_touch_points'];
        }
        return null;
    }

    /**
     * Extract device memory from fingerprint data
     */
    private function extractDeviceMemory(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['device_memory']) && !empty($fingerprint_data['device_memory'])) {
            return $fingerprint_data['device_memory'];
        }
        return 'unknown';
    }

    /**
     * Extract timezone from fingerprint data
     */
    private function extractTimezone(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['timezone']) && !empty($fingerprint_data['timezone'])) {
            return $fingerprint_data['timezone'];
        }
        return 'unknown';
    }

    /**
     * Extract canvas fingerprint from fingerprint data
     */
    private function extractCanvasFingerprint(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['canvas_fingerprint']) && !empty($fingerprint_data['canvas_fingerprint'])) {
            return $fingerprint_data['canvas_fingerprint'];
        }
        return 'unknown';
    }

    /**
     * Extract WebGL renderer from fingerprint data
     */
    private function extractWebGLRenderer(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['webgl_renderer']) && !empty($fingerprint_data['webgl_renderer'])) {
            return $fingerprint_data['webgl_renderer'];
        }
        return 'unknown';
    }

    /**
     * Extract audio sample rate from fingerprint data
     */
    private function extractAudioSampleRate(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['audio_sample_rate']) && !empty($fingerprint_data['audio_sample_rate']) && is_numeric($fingerprint_data['audio_sample_rate'])) {
            return (int) $fingerprint_data['audio_sample_rate'];
        }
        return null;
    }

    /**
     * Extract browser version from fingerprint data
     */
    private function extractBrowserVersion(array $fingerprint_data): string
    {
        // First check if browser_version is already provided
        if (isset($fingerprint_data['browser_version']) && !empty($fingerprint_data['browser_version'])) {
            return $fingerprint_data['browser_version'];
        }
        
        // Try to extract from userAgent if available
        if (isset($fingerprint_data['userAgent']) && !empty($fingerprint_data['userAgent'])) {
            $user_agent = $fingerprint_data['userAgent'];
            
            // Extract Chrome version
            if (preg_match('/Chrome\/(\d+)/', $user_agent, $matches)) {
                return 'Chrome ' . $matches[1];
            }
            
            // Extract Firefox version
            if (preg_match('/Firefox\/(\d+)/', $user_agent, $matches)) {
                return 'Firefox ' . $matches[1];
            }
            
            // Extract Safari version
            if (preg_match('/Safari\/(\d+)/', $user_agent, $matches)) {
                return 'Safari ' . $matches[1];
            }
            
            // Extract Edge version
            if (preg_match('/Edge\/(\d+)/', $user_agent, $matches)) {
                return 'Edge ' . $matches[1];
            }
        }
        
        return 'unknown';
    }

    /**
     * Extract OS version from fingerprint data
     */
    private function extractOSVersion(array $fingerprint_data): string
    {
        // First check if os_version is already provided
        if (isset($fingerprint_data['os_version']) && !empty($fingerprint_data['os_version'])) {
            return $fingerprint_data['os_version'];
        }
        
        // Try to extract from userAgent if available
        if (isset($fingerprint_data['userAgent']) && !empty($fingerprint_data['userAgent'])) {
            $user_agent = $fingerprint_data['userAgent'];
            
            // Extract Windows version
            if (preg_match('/Windows NT (\d+\.\d+)/', $user_agent, $matches)) {
                $version = $matches[1];
                if ($version === '10.0') {
                    return 'Windows 10/11';
                } elseif ($version === '6.3') {
                    return 'Windows 8.1';
                } elseif ($version === '6.2') {
                    return 'Windows 8';
                } elseif ($version === '6.1') {
                    return 'Windows 7';
                } else {
                    return 'Windows ' . $version;
                }
            }
            
            // Extract macOS version
            if (preg_match('/Mac OS X (\d+[._]\d+)/', $user_agent, $matches)) {
                $version = str_replace('_', '.', $matches[1]);
                return 'macOS ' . $version;
            }
            
            // Extract iOS version
            if (preg_match('/iPhone OS (\d+[._]\d+)/', $user_agent, $matches)) {
                $version = str_replace('_', '.', $matches[1]);
                return 'iOS ' . $version;
            }
            
            // Extract Android version
            if (preg_match('/Android (\d+\.\d+)/', $user_agent, $matches)) {
                return 'Android ' . $matches[1];
            }
            
            // Extract Linux
            if (strpos($user_agent, 'Linux') !== false) {
                return 'Linux';
            }
        }
        
        return 'unknown';
    }

    /**
     * Extract available fonts from fingerprint data
     */
    private function extractAvailableFonts(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['available_fonts']) && !empty($fingerprint_data['available_fonts'])) {
            return $fingerprint_data['available_fonts'];
        }
        return '';
    }

    /**
     * Extract font count from fingerprint data
     */
    private function extractFontCount(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['font_count']) && !empty($fingerprint_data['font_count']) && is_numeric($fingerprint_data['font_count'])) {
            return (int) $fingerprint_data['font_count'];
        }
        return null;
    }

    /**
     * Extract plugin count from fingerprint data
     */
    private function extractPluginCount(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['plugin_count']) && !empty($fingerprint_data['plugin_count']) && is_numeric($fingerprint_data['plugin_count'])) {
            return (int) $fingerprint_data['plugin_count'];
        }
        return null;
    }

    /**
     * Extract MIME type count from fingerprint data
     */
    private function extractMimeTypeCount(array $fingerprint_data): ?int
    {
        if (isset($fingerprint_data['mime_type_count']) && !empty($fingerprint_data['mime_type_count']) && is_numeric($fingerprint_data['mime_type_count'])) {
            return (int) $fingerprint_data['mime_type_count'];
        }
        return null;
    }

    /**
     * Extract media capabilities from fingerprint data
     */
    private function extractMediaCapabilities(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['media_capabilities']) && !empty($fingerprint_data['media_capabilities'])) {
            return $fingerprint_data['media_capabilities'];
        }
        return '';
    }

    /**
     * Extract battery API status from fingerprint data
     */
    private function extractBatteryAPI(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['battery_api']) && !empty($fingerprint_data['battery_api'])) {
            return $fingerprint_data['battery_api'];
        }
        return 'unknown';
    }

    /**
     * Extract connection type from fingerprint data
     */
    private function extractConnectionType(array $fingerprint_data): string
    {
        if (isset($fingerprint_data['connection_type']) && !empty($fingerprint_data['connection_type'])) {
            return $fingerprint_data['connection_type'];
        }
        return 'unknown';
    }

    /**
     * Extract network speed from fingerprint data
     */
    private function extractNetworkSpeed(array $fingerprint_data): ?float
    {
        if (isset($fingerprint_data['network_speed']) && !empty($fingerprint_data['network_speed']) && is_numeric($fingerprint_data['network_speed'])) {
            return (float) $fingerprint_data['network_speed'];
        }
        return null;
    }

    /**
     * Generate fingerprint hash from processed data
     */
    private function generateFingerprintHash(array $processed_data): string
    {
        // Create a string representation of key fingerprint data
        $fingerprint_string = implode('|', [
            $processed_data['user_agent'],
            $processed_data['screen_resolution'] ?? 'unknown',
            $processed_data['viewport_size'] ?? 'unknown',
            $processed_data['device_pixel_ratio'] ?? 'unknown',
            $processed_data['color_depth'] ?? 'unknown',
            $processed_data['hardware_cores'] ?? 'unknown',
            $processed_data['timezone'] ?? 'unknown',
            $processed_data['webgl_renderer'] ?? 'unknown',
            $processed_data['browser_version'] ?? 'unknown',
            $processed_data['os_version'] ?? 'unknown',
            $processed_data['device_memory'] ?? 'unknown',
            $processed_data['max_touch_points'] ?? 'unknown',
            $processed_data['canvas_fingerprint'] ?? 'unknown',
            $processed_data['audio_sample_rate'] ?? 'unknown'
        ]);
        
        return hash('sha256', $fingerprint_string);
    }

    /**
     * Store fingerprint data in the database
     */
    private function storeFingerprintData(int $user_id, array $processed_data, ?string $token = null): void
    {
        // Generate the enhanced device name first
        $device_name = $this->generateEnhancedDeviceName($processed_data);
        
        $fingerprint_data = [
            'user_id' => $user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'last_used_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)), // 30 days
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'device_name' => $device_name, // Add the generated device name
            
            // Fingerprint data
            'screen_resolution' => $processed_data['screen_resolution'],
            'viewport_size' => $processed_data['viewport_size'],
            'device_pixel_ratio' => $processed_data['device_pixel_ratio'],
            'color_depth' => $processed_data['color_depth'],
            'hardware_cores' => $processed_data['hardware_cores'],
            'timezone' => $processed_data['timezone'],
            'canvas_fingerprint' => $processed_data['canvas_fingerprint'],
            'webgl_renderer' => $processed_data['webgl_renderer'],
            'audio_sample_rate' => $processed_data['audio_sample_rate'],
            'fingerprint_hash' => $processed_data['fingerprint_hash'],
            'browser_version' => $processed_data['browser_version'],
            'os_version' => $processed_data['os_version'],
            'device_memory' => $processed_data['device_memory'],
            'max_touch_points' => $processed_data['max_touch_points'],
            
            // New fingerprinting fields
            'available_fonts' => $processed_data['available_fonts'],
            'font_count' => $processed_data['font_count'],
            'plugin_count' => $processed_data['plugin_count'],
            'mime_type_count' => $processed_data['mime_type_count'],
            'media_capabilities' => $processed_data['media_capabilities'],
            'battery_api' => $processed_data['battery_api'],
            'connection_type' => $processed_data['connection_type'],
            'network_speed' => $processed_data['network_speed']
        ];
        
        // Add cookie_token and token_hash if provided
        if ($token) {
            $fingerprint_data['cookie_token'] = $token;
            $fingerprint_data['token_hash'] = password_hash($token, PASSWORD_DEFAULT);
        }
        
        // Always create a new trusted device record when a new token is provided
        // This ensures each "Trust this device" action creates a unique record
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("Creating new trusted device record (fingerprint matching disabled)");
            error_log("Insert data keys: " . implode(', ', array_keys($fingerprint_data)));
            error_log("Cookie token: " . ($fingerprint_data['cookie_token'] ?? 'NOT SET'));
        }
        
        try {
            $insert_result = $this->database_service->insertOne('trusted_devices', $fingerprint_data);
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Database insert result: " . ($insert_result ? 'SUCCESS' : 'FAILED'));
                if ($insert_result) {
                    error_log("New trusted device record created with ID: " . $insert_result);
                }
            }
        } catch (Exception $e) {
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Database insert error: " . $e->getMessage());
            }
            throw $e;
        }
        
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log("Fingerprint data stored for user: " . $user_id);
        }
    }

    /**
     * Generate enhanced device name using fingerprint data
     */
    private function generateEnhancedDeviceName(array $processed_data): string
    {
        try {
            // Debug logging (only in development)
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("=== GENERATE DEVICE NAME DEBUG ===");
                error_log("Input data keys: " . implode(', ', array_keys($processed_data)));
            }
            
            // Extract and validate data with fallbacks
            $browser = $processed_data['browser_version'] ?? 'Unknown Browser';
            $os = $processed_data['os_version'] ?? 'Unknown OS';
            $screen = $processed_data['screen_resolution'] ?? 'Unknown Resolution';
            
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Extracted browser: " . $browser);
                error_log("Extracted OS: " . $os);
                error_log("Extracted screen: " . $screen);
            }
            
            // Detect device type with error handling
            try {
                $device_type = $this->detectDeviceType($processed_data);
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Detected device type: " . $device_type);
                }
            } catch (Exception $e) {
                if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    error_log("Error detecting device type: " . $e->getMessage());
                }
                $device_type = 'Desktop'; // Fallback
            }
            
            // Clean up OS name for better display
            if ($os === 'Win32') {
                $os = 'Windows';
            } elseif ($os === 'MacIntel') {
                $os = 'macOS';
            } elseif ($os === 'Linux x86_64') {
                $os = 'Linux';
            }
            
            // Clean up browser name
            if (strpos($browser, 'Chrome') !== false) {
                $browser = 'Chrome';
            } elseif (strpos($browser, 'Firefox') !== false) {
                $browser = 'Firefox';
            } elseif (strpos($browser, 'Safari') !== false) {
                $browser = 'Safari';
            } elseif (strpos($browser, 'Edge') !== false) {
                $browser = 'Edge';
            }
            
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Cleaned browser: " . $browser);
                error_log("Cleaned OS: " . $os);
            }
            
            // Generate device name
            $device_name = "{$browser} on {$os} ({$device_type})";
            
            // Add screen resolution if available and not unknown
            if ($screen && $screen !== 'unknown' && $screen !== 'Unknown Resolution') {
                $device_name .= " - {$screen}";
            }
            
            // Add hardware info if available
            if (isset($processed_data['hardware_cores']) && $processed_data['hardware_cores'] > 0) {
                $device_name .= " - {$processed_data['hardware_cores']} cores";
            }
            
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("Final device name: " . $device_name);
                error_log("=== END DEVICE NAME DEBUG ===");
            }
            
            return $device_name;
            
        } catch (Exception $e) {
            if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                error_log("=== DEVICE NAME GENERATION ERROR ===");
                error_log("Error: " . $e->getMessage());
                error_log("File: " . $e->getFile());
                error_log("Line: " . $e->getLine());
            }
            
            // Return a basic fallback device name
            return 'Unknown Device (Error)';
        }
    }

    /**
     * Detect device type based on fingerprint data
     */
    private function detectDeviceType(array $processed_data): string
    {
        $user_agent = $processed_data['user_agent'] ?? '';
        $max_touch_points = $processed_data['max_touch_points'] ?? 0;
        $screen_resolution = $processed_data['screen_resolution'] ?? '';
        
        // Check for mobile indicators (but exclude Windows with touch)
        if ((strpos($user_agent, 'Mobile') !== false || 
            strpos($user_agent, 'Android') !== false || 
            strpos($user_agent, 'iPhone') !== false) &&
            strpos($user_agent, 'Windows') === false) {
            return 'Mobile';
        }
        
        // Check for tablet indicators
        if (strpos($user_agent, 'Tablet') !== false || 
            strpos($user_agent, 'iPad') !== false) {
            return 'Tablet';
        }
        
        // Windows with touch support is still Desktop
        if (strpos($user_agent, 'Windows') !== false) {
            // Check screen resolution for device type hints
            if ($screen_resolution && $screen_resolution !== 'unknown' && $screen_resolution !== '') {
                if (strpos($screen_resolution, 'x') !== false) {
                    list($width, $height) = explode('x', $screen_resolution);
                    $width = (int)$width;
                    $height = (int)$height;
                    
                    if ($width <= 1024 || $height <= 768) {
                        return 'Small Desktop';
                    } elseif ($width >= 2560 || $height >= 1440) {
                        return 'High-Res Desktop';
                    }
                }
            }
            return 'Desktop';
        }
        
        // Check screen resolution for other device type hints
        if ($screen_resolution && $screen_resolution !== 'unknown' && $screen_resolution !== '') {
            if (strpos($screen_resolution, 'x') !== false) {
                list($width, $height) = explode('x', $screen_resolution);
                $width = (int)$width;
                $height = (int)$height;
                
                if ($width <= 1024 || $height <= 768) {
                    return 'Small Desktop';
                } elseif ($width >= 2560 || $height >= 1440) {
                    return 'High-Res Desktop';
                }
            }
        }
        
        return 'Desktop';
    }

    /**
     * Validate device fingerprint for security
     */
    public function validateDeviceFingerprint(int $user_id, string $fingerprint_hash): bool
    {
        try {
            $trusted_device = $this->database_service->findOne('trusted_devices', [
                'user_id' => $user_id,
                'fingerprint_hash' => $fingerprint_hash
            ]);
            
            return $trusted_device !== null;
        } catch (Exception $e) {
            // error_log("Error validating device fingerprint: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get device fingerprint summary for display
     */
    public function getDeviceFingerprintSummary(int $user_id): ?array
    {
        try {
            $trusted_device = $this->database_service->findOne('trusted_devices', [
                'user_id' => $user_id
            ]);
            
            if (!$trusted_device) {
                return null;
            }
            
            return [
                'device_name' => $trusted_device['device_name'],
                'screen_resolution' => $trusted_device['screen_resolution'],
                'browser_version' => $trusted_device['browser_version'],
                'os_version' => $trusted_device['os_version'],
                'timezone' => $trusted_device['timezone'],
                'last_used' => $trusted_device['last_used_at'] ?? 'Never'
            ];
        } catch (Exception $e) {
            // error_log("Error getting device fingerprint summary: " . $e->getMessage());
            return null;
        }
    }
}
