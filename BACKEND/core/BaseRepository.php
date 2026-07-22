<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey;

    public function __construct(PDO $db, string $table, string $primaryKey)
    {
        $this->db = $db;
        $this->table = $this->identifier($table);
        $this->primaryKey = $this->identifier($primaryKey);
    }

    public function findById(int $id, ?int $tenantId = null): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $params = ['id' => $id];

        if ($tenantId !== null) {
            $sql .= ' AND tenant_id = :tenant_id';
            $params['tenant_id'] = $tenantId;
        }

        $sql .= ' LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $record = $statement->fetch();

        return $record === false ? null : $record;
    }

    public function findAll(?int $tenantId = null, array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if ($tenantId !== null) {
            $where[] = 'tenant_id = :tenant_id';
            $params['tenant_id'] = $tenantId;
        }

        foreach ($filters as $column => $value) {
            $column = $this->identifier((string) $column);
            $parameter = 'filter_' . $column;
            $where[] = "{$column} = :{$parameter}";
            $params[$parameter] = $value;
        }

        $sql = "SELECT * FROM {$this->table}";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " LIMIT {$this->limit($limit)} OFFSET {$this->offset($offset)}";
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function count(?int $tenantId = null, array $filters = []): int
    {
        $where = [];
        $params = [];

        if ($tenantId !== null) {
            $where[] = 'tenant_id = :tenant_id';
            $params['tenant_id'] = $tenantId;
        }

        foreach ($filters as $column => $value) {
            $column = $this->identifier((string) $column);
            $parameter = 'filter_' . $column;
            $where[] = "{$column} = :{$parameter}";
            $params[$parameter] = $value;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    protected function identifier(string $identifier): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
            throw new InvalidArgumentException('Invalid database identifier.');
        }

        return $identifier;
    }

    protected function limit(int $limit): int
    {
        return min(max($limit, 1), 100);
    }

    protected function offset(int $offset): int
    {
        return max($offset, 0);
    }
}
