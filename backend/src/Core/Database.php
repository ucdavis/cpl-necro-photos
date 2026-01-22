<?php

namespace App\Core;

use mysqli;

class Database
{
    private static ?mysqli $connection = null;
    private string $host;
    private string $user;
    private string $password;
    private string $database;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->user = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->database = $_ENV['DB_NAME'];
    }

    /**
     * Get database connection (singleton pattern)
     */
    private function getConnection(): mysqli
    {
        if (self::$connection === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            self::$connection = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->database
            );
            
            self::$connection->set_charset('utf8mb4');
        }

        return self::$connection;
    }

    /**
     * Execute a SELECT query and return results
     */
    public function query(string $sql, array $params = []): array
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    /**
     * Execute an INSERT/UPDATE/DELETE query
     */
    public function execute(string $sql, array $params = []): bool
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId(): int
    {
        return $this->getConnection()->insert_id;
    }

    /**
     * Get affected rows from last query
     */
    public function affectedRows(): int
    {
        return $this->getConnection()->affected_rows;
    }

    /**
     * Determine parameter types for bind_param
     */
    private function getParamTypes(array $params): string
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }

    /**
     * Close the database connection
     */
    public function close(): void
    {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
