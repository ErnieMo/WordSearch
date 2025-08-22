<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use Exception;

class DatabaseService
{
    private PDO $pdo;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = "pgsql:host={$this->config['DB_HOST']};port={$this->config['DB_PORT']};dbname={$this->config['DB_DATABASE']}";
            
            $this->pdo = new PDO($dsn, $this->config['DB_USERNAME'], $this->config['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollback();
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Execute failed: " . $e->getMessage());
        }
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders}) RETURNING id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            $result = $stmt->fetch();
            return $result['id'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data)));
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_merge($data, $whereParams));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }

    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereParams);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }

    public function findOne(string $table, string $where, array $whereParams = []): ?array
    {
        $sql = "SELECT * FROM {$table} WHERE {$where} LIMIT 1";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereParams);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            throw new Exception("Find failed: " . $e->getMessage());
        }
    }

    public function findMany(string $table, string $where = '1=1', array $whereParams = [], string $orderBy = '', int $limit = 0): array
    {
        $sql = "SELECT * FROM {$table} WHERE {$where}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereParams);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Find many failed: " . $e->getMessage());
        }
    }

    public function count(string $table, string $where = '1=1', array $whereParams = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereParams);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (PDOException $e) {
            throw new Exception("Count failed: " . $e->getMessage());
        }
    }
}
