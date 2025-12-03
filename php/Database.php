<?php
class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    // Secure query execution
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Get single row
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Get multiple rows
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    // Get last insert ID
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    // Close connection
    public function close() {
        $this->conn->close();
    }
    
    // Escape string for security
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}
?>