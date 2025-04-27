<?php
require_once __DIR__ . '/config.php';

class Database {
    private $connection;

    public function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }

    public function selectOne($query, $params = []) {
        $stmt = $this->prepareAndExecute($query, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Return a single row
    }

    public function selectAll($query, $params = []) {
        $stmt = $this->prepareAndExecute($query, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC); // Return all rows
    }

    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->prepareAndExecute($query, array_values($data));
        return $this->connection->insert_id; // Return the last inserted ID
    }

    public function update($table, $data, $condition, $conditionParams = []) {
        $columns = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
        $query = "UPDATE $table SET $columns WHERE $condition";
        $this->prepareAndExecute($query, array_merge(array_values($data), $conditionParams));
    }

    public function delete($table, $condition, $conditionParams = []) {
        $query = "DELETE FROM $table WHERE $condition";
        $this->prepareAndExecute($query, $conditionParams);
    }

    private function prepareAndExecute($query, $params) {
        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            die("Database query preparation failed: " . $this->connection->error);
        }

        if (!empty($params)) {
            $types = str_repeat("s", count($params)); // Assuming all parameters are strings
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }
}

// Create a singleton instance of the Database class
function db() {
    static $database;
    if (!$database) {
        $database = new Database();
    }
    return $database;
}
