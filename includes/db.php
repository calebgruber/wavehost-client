<?php
class Database {
    private $connection;

    public function __construct() {
        $this->connection = new PDO(
            'mysql:host=localhost;dbname=voxelnodes_waveclient',
            'voxelnodes_waveclient',
            'pLoH,K_POeUz'
        );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function select($query, $params = []) {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectOne($query, $params = []) {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array_values($data));
        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $condition, $conditionParams = []) {
        $columns = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
        $query = "UPDATE $table SET $columns WHERE $condition";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array_merge(array_values($data), $conditionParams));
    }
}

function db() {
    static $database;
    if (!$database) {
        $database = new Database();
    }
    return $database;
}
