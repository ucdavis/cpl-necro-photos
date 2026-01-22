<?php

namespace App\Core;

use App\Core\Database;

abstract class Repository
{
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Execute a query with parameters
     */
    protected function query(string $sql, array $params = []): array
    {
        return $this->db->query($sql, $params);
    }

    /**
     * Execute a query and return a single row
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $results = $this->db->query($sql, $params);
        return $results[0] ?? null;
    }

    /**
     * Execute an insert/update/delete query
     */
    protected function execute(string $sql, array $params = []): bool
    {
        return $this->db->execute($sql, $params);
    }

    /**
     * Get the last inserted ID
     */
    protected function lastInsertId(): int
    {
        return $this->db->lastInsertId();
    }
}
