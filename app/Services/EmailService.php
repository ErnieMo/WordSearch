<?php

declare(strict_types=1);

namespace Sudoku\Services;

/**
 * Email Service for Sudoku
 * 
 * Handles email sending with comprehensive logging functionality.
 * 
 * @author Sudoku Team
 * @version 1.0.0
 * @since 2024-01-01
 */

// Log file inclusion for debugging
//error_log("\n\n" . __FILE__ . PHP_EOL, 3, __DIR__ . '/../../../../Logs/included_files.log');

/**
 * Email service class
 */
class EmailService
{
    private string $mail_host;
    private string $mail_port;
    private string $mail_username;
    private string $mail_password;
    private string $mail_encryption;
    private string $mail_from_address;
    private string $mail_from_name;

    public function __construct()
    {
        $this->mail_host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->mail_port = $_ENV['MAIL_PORT'] ?? '587';
        $this->mail_username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mail_password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mail_encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $this->mail_from_address = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@sudoku.nofinway.com';
        $this->mail_from_name = $_ENV['MAIL_FROM_NAME'] ?? 'Sudoku';
    }

    /**
     * Send email with logging
     */
    public function sendEmail(string $to_email, string $subject, string $message, string $type = 'general'): array
    {
        $start_time = microtime(true);
        
        try {
            // Check if we have SMTP configuration
            if (!empty($this->mail_host) && !empty($this->mail_username) && !empty($this->mail_password)) {
                $result = $this->sendSMTPEmail($to_email, $subject, $message);
            } else {
                $result = $this->sendBasicEmail($to_email, $subject, $message);
            }
            
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            $result['execution_time'] = $execution_time;
            $result['type'] = $type;
            
            // Log the email attempt
            $this->logEmail($to_email, $subject, $message, $result);
            
            return $result;
        } catch (\Exception $e) {
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            $error_result = [
                'success' => false,
                'error' => 'Email sending failed: ' . $e->getMessage(),
                'execution_time' => $execution_time,
                'type' => $type
            ];
            
            // Log the error
            $this->logEmail($to_email, $subject, $message, $error_result);
            
            return $error_result;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $to_email, string $reset_url): array
    {
        $subject = 'Password Reset Request - Sudoku';
        $message = $this->getPasswordResetTemplate($reset_url);
        
        return $this->sendEmail($to_email, $subject, $message, 'password_reset');
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail(string $to_email, string $username): array
    {
        $subject = 'Welcome to Sudoku!';
        $message = $this->getWelcomeTemplate($username);
        
        return $this->sendEmail($to_email, $subject, $message, 'welcome');
    }

    /**
     * Send game completion email
     */
    public function sendGameCompletionEmail(string $to_email, string $username, array $game_stats): array
    {
        $subject = 'Congratulations! Game Completed - Sudoku';
        $message = $this->getGameCompletionTemplate($username, $game_stats);
        
        return $this->sendEmail($to_email, $subject, $message, 'game_completion');
    }

    /**
     * Send SMTP email
     */
    private function sendSMTPEmail(string $to_email, string $subject, string $message): array
    {
        try {
            // Create email headers
            $headers = [
                'From: ' . $this->mail_from_name . ' <' . $this->mail_from_address . '>',
                'Reply-To: ' . $this->mail_from_address,
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'X-Mailer: PHP/' . PHP_VERSION
            ];
            
            // Create HTML message
            $html_message = $this->wrapInHTMLTemplate($message);
            
            // Try to send using SMTP if possible
            if (function_exists('fsockopen')) {
                $smtp_result = $this->sendSMTP($to_email, $subject, $html_message);
                if ($smtp_result['success']) {
                    return $smtp_result;
                }
            }
            
            // Fallback to mail() function
            $result = mail($to_email, $subject, $html_message, implode("\r\n", $headers));
            
            if ($result) {
                return [
                    'success' => true,
                    'method' => 'mail() function',
                    'message' => 'Email sent successfully using PHP mail() function'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to send email using mail() function'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'SMTP email failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email using basic mail() function
     */
    private function sendBasicEmail(string $to_email, string $subject, string $message): array
    {
        $headers = [
            'From: ' . $this->mail_from_name . ' <' . $this->mail_from_address . '>',
            'Reply-To: ' . $this->mail_from_address,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . PHP_VERSION
        ];
        
        $html_message = $this->wrapInHTMLTemplate($message);
        
        $result = mail($to_email, $subject, $html_message, implode("\r\n", $headers));
        
        if ($result) {
            return [
                'success' => true,
                'method' => 'mail() function',
                'message' => 'Email sent successfully using PHP mail() function'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to send email using mail() function'
            ];
        }
    }

    /**
     * Simple SMTP implementation
     */
    private function sendSMTP(string $to_email, string $subject, string $message): array
    {
        try {
            $socket = fsockopen($this->mail_host, (int)$this->mail_port, $errno, $errstr, 30);
            if (!$socket) {
                return [
                    'success' => false,
                    'error' => "Could not connect to {$this->mail_host}:{$this->mail_port} - $errstr ($errno)"
                ];
            }
            
            // Read server greeting
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'Server not ready: ' . $response
                ];
            }
            
            // Send EHLO
            fputs($socket, "EHLO " . $this->mail_host . "\r\n");
            $response = fgets($socket, 515);
            
            // Start TLS if required
            if ($this->mail_encryption === 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $response = fgets($socket, 515);
                if (substr($response, 0, 3) != '220') {
                    fclose($socket);
                    return [
                        'success' => false,
                        'error' => 'TLS not supported: ' . $response
                    ];
                }
                
                // Enable TLS
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($socket);
                    return [
                        'success' => false,
                        'error' => 'Failed to enable TLS'
                    ];
                }
                
                // Send EHLO again after TLS
                fputs($socket, "EHLO " . $this->mail_host . "\r\n");
                $response = fgets($socket, 515);
            }
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'Authentication not supported: ' . $response
                ];
            }
            
            fputs($socket, base64_encode($this->mail_username) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'Username rejected: ' . $response
                ];
            }
            
            fputs($socket, base64_encode($this->mail_password) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'Password rejected: ' . $response
                ];
            }
            
            // Send email
            fputs($socket, "MAIL FROM: <{$this->mail_from_address}>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'MAIL FROM failed: ' . $response
                ];
            }
            
            fputs($socket, "RCPT TO: <$to_email>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'RCPT TO failed: ' . $response
                ];
            }
            
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'DATA command failed: ' . $response
                ];
            }
            
            $headers = [
                'From: ' . $this->mail_from_name . ' <' . $this->mail_from_address . '>',
                'To: <' . $to_email . '>',
                'Subject: ' . $subject,
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'X-Mailer: PHP/' . PHP_VERSION
            ];
            
            $email_data = implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.\r\n";
            fputs($socket, $email_data);
            
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return [
                    'success' => false,
                    'error' => 'Email sending failed: ' . $response
                ];
            }
            
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            return [
                'success' => true,
                'method' => 'SMTP',
                'message' => 'Email sent successfully using SMTP'
            ];
            
        } catch (\Exception $e) {
            if (isset($socket)) {
                fclose($socket);
            }
            return [
                'success' => false,
                'error' => 'SMTP error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log email attempts to email.log
     */
    private function logEmail(string $to_email, string $subject, string $message, array $result): void
    {
        $log_file = __DIR__ . '/../../../../Logs/email.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $request_uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        // Create logs directory if it doesn't exist
        $logs_dir = dirname($log_file);
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
        
        // Prepare log entry
        $log_entry = [
            'timestamp' => $timestamp,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'request_uri' => $request_uri,
            'to_email' => $to_email,
            'subject' => $subject,
            'message_length' => strlen($message),
            'success' => $result['success'] ? 'true' : 'false',
            'method' => $result['method'] ?? 'unknown',
            'type' => $result['type'] ?? 'general',
            'execution_time' => $result['execution_time'] ?? 0,
            'error' => $result['error'] ?? null
        ];
        
        // Format log line
        $log_line = sprintf(
            "[%s] %s | To: %s | Subject: %s | Success: %s | Method: %s | Type: %s | IP: %s | Time: %sms",
            $timestamp,
            $result['success'] ? 'SUCCESS' : 'FAILED',
            $to_email,
            $subject,
            $result['success'] ? 'true' : 'false',
            $result['method'] ?? 'unknown',
            $result['type'] ?? 'general',
            $ip_address,
            $result['execution_time'] ?? 0
        );
        
        if (!$result['success'] && isset($result['error'])) {
            $log_line .= " | Error: " . $result['error'];
        }
        
        $log_line .= PHP_EOL;
        
        // Write to log file
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Also log detailed JSON entry for debugging
        $json_log_file = __DIR__ . '/../../../../Logs/email_detailed.log';
        $json_entry = json_encode($log_entry, JSON_PRETTY_PRINT) . PHP_EOL;
        file_put_contents($json_log_file, $json_entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Wrap message in HTML template
     */
    private function wrapInHTMLTemplate(string $message): string
    {
        return '
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sudoku</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 style="color: #007bff; margin: 0;">🎮 Sudoku</h1>
                    <p style="color: #6c757d; margin: 10px 0 0 0;">The Ultimate Sudoku Experience</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    ' . $message . '
                </div>
                
                <hr style="border: none; border-top: 1px solid #dee2e6; margin: 30px 0;">
                
                <div style="text-align: center; color: #6c757d; font-size: 12px;">
                    <p style="margin: 0;">
                        This email was sent from the Sudoku application.<br>
                        Sent on: ' . date('Y-m-d H:i:s') . '<br>
                        If you did not expect this email, please ignore it.
                    </p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Get password reset email template
     */
    private function getPasswordResetTemplate(string $reset_url): string
    {
        return "
        <h2 style='color: #007bff; margin-top: 0;'>Password Reset Request</h2>
        
        <p>You have requested to reset your password for your Sudoku account.</p>
        
        <p>To reset your password, please click the button below:</p>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='{$reset_url}' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
        </div>
        
        <p><strong>Important:</strong></p>
        <ul>
            <li>This link will expire in 1 hour</li>
            <li>If you did not request this password reset, please ignore this email</li>
            <li>For security reasons, this link can only be used once</li>
        </ul>
        
        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
        <p style='word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace;'>{$reset_url}</p>";
    }

    /**
     * Get welcome email template
     */
    private function getWelcomeTemplate(string $username): string
    {
        return "
        <h2 style='color: #007bff; margin-top: 0;'>Welcome to Sudoku!</h2>
        
        <p>Hi <strong>{$username}</strong>,</p>
        
        <p>Welcome to Sudoku! We're excited to have you join our community of puzzle enthusiasts.</p>
        
        <h3 style='color: #28a745;'>What you can do:</h3>
        <ul>
            <li>Play Sudoku puzzles at multiple difficulty levels</li>
            <li>Track your progress and statistics</li>
            <li>Compete on leaderboards</li>
            <li>Save and resume games</li>
            <li>Get hints when you're stuck</li>
        </ul>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='" . ($_ENV['APP_URL'] ?? 'https://sudoku.nofinway.com') . "/game' style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Start Playing Now</a>
        </div>
        
        <p>Happy puzzling!</p>
        <p>The Sudoku Team</p>";
    }

    /**
     * Get game completion email template
     */
    private function getGameCompletionTemplate(string $username, array $game_stats): string
    {
        $difficulty = $game_stats['difficulty'] ?? 'Unknown';
        $elapsed_time = $game_stats['elapsed_time'] ?? 0;
        $hints_used = $game_stats['hints_used'] ?? 0;
        
        $time_formatted = gmdate('H:i:s', $elapsed_time);
        
        return "
        <h2 style='color: #007bff; margin-top: 0;'>🎉 Congratulations!</h2>
        
        <p>Hi <strong>{$username}</strong>,</p>
        
        <p>Congratulations! You've successfully completed a Sudoku puzzle!</p>
        
        <div style='background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 20px; margin: 20px 0;'>
            <h3 style='color: #155724; margin-top: 0;'>Game Statistics:</h3>
            <ul style='margin: 0; padding-left: 20px;'>
                <li><strong>Difficulty:</strong> {$difficulty}</li>
                <li><strong>Completion Time:</strong> {$time_formatted}</li>
                <li><strong>Hints Used:</strong> {$hints_used}</li>
            </ul>
        </div>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='" . ($_ENV['APP_URL'] ?? 'https://sudoku.nofinway.com') . "/game' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Play Another Game</a>
        </div>
        
        <p>Keep up the great work and challenge yourself with harder puzzles!</p>
        <p>The Sudoku Team</p>";
    }
} 