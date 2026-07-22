<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Base model class providing common database operations.
 * Extended by feature-specific models for consistent DB access patterns.
 */
abstract class BaseModel
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }

    /**
     * Find a record by its primary key.
     *
     * @param int|string $id Primary key value
     * @return array|null Record data or null if not found
     */
    public function find($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all records matching optional conditions.
     *
     * @param array $conditions Column => value pairs for WHERE clause
     * @param int|null $limit Maximum number of records
     * @param int $offset Offset for pagination
     * @return array List of records
     */
    public function all(array $conditions = [], ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $column => $value) {
                $clauses[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new record using fillable fields.
     *
     * @param array $data Data to insert (only fillable fields are used)
     * @return int|string Last insert ID
     */
    public function create(array $data)
    {
        $fillable = array_intersect_key($data, array_flip($this->fillable));
        $columns = array_keys($fillable);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->db->prepare($sql);
        $params = [];
        foreach ($fillable as $column => $value) {
            $params[":{$column}"] = $value;
        }
        $stmt->execute($params);

        return $this->db->lastInsertId();
    }

    /**
     * Update a record by primary key.
     *
     * @param int|string $id Primary key value
     * @param array $data Data to update (only fillable fields are used)
     * @return bool True on success
     */
    public function update($id, array $data): bool
    {
        $fillable = array_intersect_key($data, array_flip($this->fillable));
        if (empty($fillable)) {
            return false;
        }

        $clauses = [];
        $params = [];
        foreach ($fillable as $column => $value) {
            $clauses[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }

        $params[':id'] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $clauses) . " 
                WHERE {$this->primaryKey} = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a record by primary key.
     *
     * @param int|string $id Primary key value
     * @return bool True on success
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Count records matching optional conditions.
     *
     * @param array $conditions Column => value pairs for WHERE clause
     * @return int Record count
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $column => $value) {
                $clauses[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Execute a raw query with optional parameters.
     *
     * @param string $sql SQL query
     * @param array $params Bound parameters
     * @return PDOStatement Executed statement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
