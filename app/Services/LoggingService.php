<?php

declare(strict_types=1);

namespace Sudoku\Services;

/**
 * Centralized Logging Service
 * 
 * Handles all application logging with environment-based control.
 * Logging only occurs when APP_DEBUG=true in environment.
 * 
 * @author Sudoku Game Team
 * @version 1.0.0
 * @since 2025-08-15
 */

class LoggingService
{
    private bool $debug_enabled;
    private string $database_log_file;
    private string $games_log_file;
    private string $email_log_file;

    public function __construct()
    {
        $this->debug_enabled = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        $this->database_log_file = __DIR__ . '/../../logs/database.log';
        $this->games_log_file = __DIR__ . '/../../logs/games.log';
        $this->email_log_file = __DIR__ . '/../../logs/email.log';
    }

    /**
     * Check if debug logging is enabled
     */
    public function isDebugEnabled(): bool
    {
        return $this->debug_enabled;
    }

    /**
     * Log to database.log file
     */
    public function logToDatabase(string $operation, array $data = []): void
    {
        if (!$this->debug_enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'operation' => $operation,
            'data' => $data
        ];
        
        $log_line = json_encode($log_entry) . PHP_EOL;
        file_put_contents($this->database_log_file, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log to games.log file
     */
    public function logToGames(string $operation, array $data = []): void
    {
        if (!$this->debug_enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'operation' => $operation,
            'data' => $data
        ];
        
        $log_line = json_encode($log_entry) . PHP_EOL;
        file_put_contents($this->games_log_file, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log to email.log file
     */
    public function logToEmail(string $operation, array $data = []): void
    {
        if (!$this->debug_enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'operation' => $operation,
            'data' => $data
        ];
        
        $log_line = json_encode($log_entry) . PHP_EOL;
        file_put_contents($this->email_log_file, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Generic log method that writes to a specified file
     */
    public function logToFile(string $file_path, string $operation, array $data = []): void
    {
        if (!$this->debug_enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'operation' => $operation,
            'data' => $data
        ];
        
        $log_line = json_encode($log_entry) . PHP_EOL;
        file_put_contents($file_path, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log with controller context
     */
    public function logWithController(string $file_path, string $operation, string $controller, array $data = []): void
    {
        if (!$this->debug_enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'operation' => $operation,
            'controller' => $controller,
            'data' => $data
        ];
        
        $log_line = json_encode($log_entry) . PHP_EOL;
        file_put_contents($file_path, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log database operation with table context
     */
    public function logDatabaseOperation(string $operation, string $table, array $data = [], ?string $result = null): void
    {
        if (!$this->debug_enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = [
            'timestamp' => $timestamp,
            'operation' => $operation,
            'table' => $table,
            'data' => $data,
            'result' => $result
        ];
        
        $log_line = json_encode($log_entry) . PHP_EOL;
        file_put_contents($this->database_log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
}
