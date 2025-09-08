<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use PDOStatement;

class DatabaseService
{
    private ?PDO $connection = null;
    private array $config;
    private string $logFile;
    private ?string $currentLogEntry = null;

    public function __construct()
    {
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_DATABASE'] ?? 'nofinway_dev',
            'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password' => $_ENV['DB_PASSWORD'] ?? ''
        ];
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']}";
                $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->connection;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        // Log the query with file location and line number
        $this->logQuery($sql, $params);
        
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            // Log success
            $this->logQueryResult('success');
            
            return $stmt;
        } catch (PDOException $e) {
            // Log failure with reason
            $this->logQueryResult('failure', $e->getMessage());
            throw $e;
        }
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders}) RETURNING id";
        $stmt = $this->query($sql, $data);
        
        return (int)$stmt->fetch()['id'];
    }

    public function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        
        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = "{$column} = :where_{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);
        
        $params = $data;
        foreach ($where as $column => $value) {
            $params["where_{$column}"] = $value;
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = "{$column} = :{$column}";
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereParts);
        $stmt = $this->query($sql, $where);
        
        return $stmt->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }

    public function isConnected(): bool
    {
        try {
            // Log the connection test query
            $this->logQuery('SELECT 1', []);
            $this->getConnection()->query('SELECT 1');
            $this->logQueryResult('success');
            return true;
        } catch (PDOException $e) {
            $this->logQueryResult('failure', $e->getMessage());
            return false;
        }
    }

    public function close(): void
    {
        $this->connection = null;
    }

    /**
     * Log database queries to the database log file
     */
    private function logQuery(string $sql, array $params = []): void
    {
        $this->logFile = '/var/www/html/WordSearch/Dev/log/database.log';
        
        // Get the calling file and line number
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $callingFile = 'Unknown';
        $callingLine = 0;
        
        // Find the first caller that's not in DatabaseService
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && strpos($trace['file'], 'DatabaseService.php') === false) {
                $callingFile = $trace['file'];
                $callingLine = $trace['line'];
                break;
            }
        }
        
        // Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $relativePath = str_replace('/var/www/html/WordSearch/Dev/', '', $callingFile);
        
        $this->currentLogEntry = "[{$timestamp}] File: {$relativePath}:{$callingLine}\n";
        $this->currentLogEntry .= "SQL: {$sql}\n";
        
        if (!empty($params)) {
            $this->currentLogEntry .= "Parameters: " . json_encode($params, JSON_PRETTY_PRINT) . "\n";
        }
        
        // Don't add the double newline yet - wait for the result
    }

    /**
     * Log the result of a database query
     */
    private function logQueryResult(string $status, string $errorMessage = ''): void
    {
        if (!isset($this->currentLogEntry)) {
            return;
        }

        // Add the result to the current log entry
        if ($status === 'success') {
            $this->currentLogEntry .= "Result: (success)\n";
        } else {
            $this->currentLogEntry .= "Result: (failure - {$errorMessage})\n";
        }
        
        $this->currentLogEntry .= "\n\n"; // Double newline between queries as requested
        
        // Ensure the log directory exists and is writable
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to log file with file locking
        file_put_contents($this->logFile, $this->currentLogEntry, FILE_APPEND | LOCK_EX);
        
        // Clear the current log entry
        $this->currentLogEntry = null;
    }
}
