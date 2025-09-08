<?php
/**
 * Sudoku - Admin Controller
 * 
 * Handles administrative user management and system overview.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

declare(strict_types=1);

namespace Sudoku\Controllers;

/**
 * Sudoku - Admin Controller
 * 
 * Handles administrative user management and system overview.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2024-01-01
 */

use Sudoku\Core\BaseController;
use Sudoku\Services\DatabaseService;
use Sudoku\Services\SessionService;
use Exception;

/**
 * Admin controller for user management
 */
class AdminController extends BaseController
{
    /**
     * Constructor - initializes required services
     */
    public function __construct()
    {
        $database_service = new DatabaseService();
        $logging_service = new \Sudoku\Services\LoggingService();
        $session_service = new SessionService($database_service, $logging_service);
        parent::__construct($database_service, $session_service);
    }

    /**
     * Show users management page
     * 
     * @return void
     * @throws Exception When database operations fail
     */
    public function showUsers(): void
    {
        $this->requireAdmin();
        $user = $this->getCurrentUser();

        // Get data for the view
        try {
            $users = $this->getUsersWithLoginHistory();
            $active_users = $this->getActiveUsers();
            $trusted_devices = $this->getTrustedDevicesInfo();
            $recent_logins = $this->getRecentLoginHistory();
            
            // Format data for view display
            $users = $this->formatUsersForView($users);
            $active_users = $this->formatActiveUsersForView($active_users);
            $trusted_devices = $this->formatTrustedDevicesForView($trusted_devices);
            $recent_logins = $this->formatRecentLoginsForView($recent_logins);
            
        } catch (Exception $e) {
            // Use error_log for now since LoggingService doesn't have logError method
            error_log('AdminController::showUsers error: ' . $e->getMessage());
            $users = [];
            $active_users = [];
            $trusted_devices = [];
            $recent_logins = [];
        }

        $this->render('admin/users', [
            'user' => $user,
            'users' => $users,
            'active_users' => $active_users,
            'trusted_devices' => $trusted_devices,
            'recent_logins' => $recent_logins
        ]);
    }

    /**
     * Get all users with their last login information
     * 
     * @return array Array of users with login history
     */
    private function getUsersWithLoginHistory(): array
    {
        try {
            // Get all users
            $users = $this->database_service->find('users', []);
            
            // Get login history for each user
            foreach ($users as &$user_data) {
                // Get trusted devices count
                $trusted_devices = $this->database_service->find('trusted_devices', ['user_id' => $user_data['id']]);
                $user_data['trusted_devices_count'] = count($trusted_devices);
                
                // Get last successful login from user_logins table (if it exists)
                try {
                    $last_login = $this->database_service->findOne('user_logins', [
                        'user_id' => $user_data['id'],
                        'success' => true
                    ], ['order_by' => 'login_timestamp DESC']);
                    
                    $user_data['last_login_at'] = $last_login ? $last_login['login_timestamp'] : null;
                } catch (Exception $e) {
                    // user_logins table might not exist yet
                    $user_data['last_login_at'] = null;
                }
                
                // Get last device activity from trusted devices
                if (!empty($trusted_devices)) {
                    $user_data['last_device_activity'] = max(array_column($trusted_devices, 'last_used_at'));
                } else {
                    $user_data['last_device_activity'] = null;
                }
            }
            
            // Sort by last login (most recent first)
            usort($users, function($a, $b) {
                $a_time = $a['last_login_at'] ? strtotime($a['last_login_at']) : 0;
                $b_time = $b['last_login_at'] ? strtotime($b['last_login_at']) : 0;
                return $b_time - $a_time;
            });
            
            return $users;
            
        } catch (Exception $e) {
            // Use error_log for now since LoggingService doesn't have logError method
            error_log('Error fetching users with login history: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get currently active users (users with recent activity)
     * 
     * @return array Array of active users
     */
    private function getActiveUsers(): array
    {
        try {
            // Consider users active if they've logged in within the last 24 hours
            $active_threshold = date('Y-m-d H:i:s', strtotime('-24 hours'));
            
            // Try to get recent successful logins from user_logins table
            try {
                $recent_logins = $this->database_service->find('user_logins', [
                    'success' => true
                ]);
                
                $active_users = [];
                foreach ($recent_logins as $login) {
                    if ($login['login_timestamp'] >= $active_threshold) {
                        $user = $this->database_service->findOne('users', ['id' => $login['user_id']]);
                        if ($user) {
                            // Get device information if available
                            $device_name = $login['device_name'] ?? 'Unknown';
                            $ip_address = $login['ip_address'] ?? 'Unknown';
                            $user_agent = $login['user_agent'] ?? 'Unknown';
                            
                            $active_users[] = [
                                'id' => $user['id'],
                                'username' => $user['username'],
                                'email' => $user['email'],
                                'last_login_at' => $login['login_timestamp'],
                                'admin' => $user['isadmin'] ?? false,
                                'device_name' => $device_name,
                                'ip_address' => $ip_address,
                                'last_used_at' => $login['login_timestamp'],
                                'user_agent' => $user_agent
                            ];
                        }
                    }
                }
                
                return $active_users;
                
            } catch (Exception $e) {
                // user_logins table might not exist yet, return empty array
                return [];
            }
            
        } catch (Exception $e) {
            // Use error_log for now since LoggingService doesn't have logError method
            error_log('Error fetching active users: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trusted devices information
     * 
     * @return array Array of trusted devices with user info
     */
    private function getTrustedDevicesInfo(): array
    {
        try {
            // Get all trusted devices (including expired ones for admin view)
            // We'll show expired ones but mark them as such
            $trusted_devices = $this->database_service->find('trusted_devices', []);
            
            // Join with user data and add status information
            foreach ($trusted_devices as &$device) {
                $user = $this->database_service->findOne('users', ['id' => $device['user_id']]);
                if ($user) {
                    $device['username'] = $user['username'];
                    $device['email'] = $user['email'];
                }
                
                // Determine device status
                $current_time = date('Y-m-d H:i:s');
                if (empty($device['expires_at'])) {
                    $device['status'] = 'No Expiry';
                    $device['is_expired'] = false;
                } elseif (strtotime($device['expires_at']) > strtotime($current_time)) {
                    $device['status'] = 'Active';
                    $device['is_expired'] = false;
                } else {
                    $device['status'] = 'Expired';
                    $device['is_expired'] = true;
                }
            }
            
            return $trusted_devices;
            
        } catch (Exception $e) {
            // Use error_log for now since LoggingService doesn't have logError method
            error_log('Error fetching trusted devices info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent login history
     * 
     * @return array Array of recent login records
     */
    private function getRecentLoginHistory(): array
    {
        try {
            // Get recent successful logins from user_logins table
            $recent_logins = $this->database_service->find('user_logins', [
                'success' => true
            ], ['order_by' => 'login_timestamp DESC', 'limit' => 10]); // Limit to 10 for recent history
            
            $formatted_logins = [];
            foreach ($recent_logins as $login) {
                $user = $this->database_service->findOne('users', ['id' => $login['user_id']]);
                if ($user) {
                    $formatted_logins[] = [
                        'id' => $login['id'],
                        'user_id' => $login['user_id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'device_name' => $login['device_name'] ?? 'Unknown',
                        'ip_address' => $login['ip_address'] ?? 'Unknown',
                        'user_agent' => $login['user_agent'] ?? 'Unknown',
                        'login_timestamp' => $login['login_timestamp'],
                        'success' => $login['success']
                    ];
                }
            }
            
            return $formatted_logins;
            
        } catch (Exception $e) {
            // user_logins table might not exist yet, return empty array
            return [];
        }
    }

    /**
     * Format users data for view display
     * 
     * @param array $users Array of users to format
     * @return array Formatted users array
     */
    private function formatUsersForView(array $users): array
    {
        foreach ($users as &$user) {
            if ($user['last_login_at']) {
                $user['last_login_formatted'] = $this->formatTimeAgo($user['last_login_at']);
            }
            if ($user['last_device_activity']) {
                $user['last_device_activity_formatted'] = $this->formatTimeAgo($user['last_device_activity']);
            }
            if ($user['created_at']) {
                $user['created_formatted'] = $this->formatTimeAgo($user['created_at']);
            }
        }
        return $users;
    }

    /**
     * Format active users data for view display
     * 
     * @param array $active_users Array of active users to format
     * @return array Formatted active users array
     */
    private function formatActiveUsersForView(array $active_users): array
    {
        foreach ($active_users as &$user) {
            if ($user['last_used_at']) {
                $user['last_used_formatted'] = $this->formatTimeAgo($user['last_used_at']);
            }
        }
        return $active_users;
    }

    /**
     * Format trusted devices data for view display
     * 
     * @param array $trusted_devices Array of trusted devices to format
     * @return array Formatted trusted devices array
     */
    private function formatTrustedDevicesForView(array $trusted_devices): array
    {
        foreach ($trusted_devices as &$device) {
            if ($device['last_used_at']) {
                $device['last_used_formatted'] = $this->formatTimeAgo($device['last_used_at']);
            }
            if ($device['expires_at']) {
                $device['expires_formatted'] = $this->formatExpiryDate($device['expires_at']);
            }
        }
        return $trusted_devices;
    }

    /**
     * Format recent logins data for view display
     * 
     * @param array $recent_logins Array of recent logins to format
     * @return array Formatted recent logins array
     */
    private function formatRecentLoginsForView(array $recent_logins): array
    {
        foreach ($recent_logins as &$login) {
            if ($login['login_timestamp']) {
                $login['login_formatted'] = $this->formatTimeAgo($login['login_timestamp']);
            }
        }
        return $recent_logins;
    }

    /**
     * Format timestamp to human-readable "time ago" string
     * 
     * @param string $timestamp Timestamp to format
     * @return string Formatted time string
     */
    public function formatTimeAgo(string $timestamp): string
    {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;
        
        // Debug output to see what's happening
        // error_log("DEBUG formatTimeAgo: timestamp='$timestamp', time=$time, now=$now, diff=$diff seconds\n\n", 3, "/var/www/html/Sudoku/Dev/logs/debug.log");

        
        // If we get a negative difference, the timestamp might be in the future
        // This can happen with timezone issues. Let's try to normalize it.
        if ($diff < 0) {
            // Try parsing without timezone info
            $clean_timestamp = preg_replace('/-\d{2}$/', '', $timestamp);
            $time = strtotime($clean_timestamp);
            $diff = $now - $time;
            error_log("DEBUG formatTimeAgo: Cleaned timestamp='$clean_timestamp', new time=$time, new diff=$diff seconds", 3, "/var/www/html/Sudoku/Dev/logs/debug.log");
        }
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Format expiry date to human-readable string
     * 
     * @param string $expiry_timestamp Expiry timestamp to format
     * @return string Formatted expiry string
     */
    public function formatExpiryDate(string $expiry_timestamp): string
    {
        $expiry_time = strtotime($expiry_timestamp);
        $now = time();
        $diff = $expiry_time - $now; // Positive = future, negative = past
        
        if ($diff < 0) {
            // Expired
            $days_ago = abs(floor($diff / 86400));
            if ($days_ago == 0) {
                return 'Expired today';
            } elseif ($days_ago == 1) {
                return 'Expired yesterday';
            } else {
                return $days_ago . ' day' . ($days_ago > 1 ? 's' : '') . ' ago';
            }
        } elseif ($diff < 86400) {
            // Expires today
            $hours = floor($diff / 3600);
            if ($hours == 0) {
                $minutes = floor($diff / 60);
                return 'Expires in ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '';
            } else {
                return 'Expires in ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . '';
            }
        } elseif ($diff < 2592000) {
            // Expires in days
            $days = floor($diff / 86400);
            return 'Expires in ' . $days . ' day' . ($days > 1 ? 's' : '') . '';
        } elseif ($diff < 31536000) {
            // Expires in months
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            // Expires in years
            $years = floor($diff / 31536000);
            return 'Expires in ' . $years . ' year' . ($years > 1 ? 's' : '') . '';
        }
    }

    /**
     * Truncate user agent string for display
     * 
     * @param string $userAgent User agent string to truncate
     * @return string Truncated user agent string
     */
    public function truncateUserAgent(string $userAgent): string
    {
        if (strlen($userAgent) <= 50) {
            return $userAgent;
        }
        return substr($userAgent, 0, 47) . '...';
    }
}
