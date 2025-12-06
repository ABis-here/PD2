<?php
class Database {
    private $conn;
    
    public function __construct() {
        require_once 'includes/config.php';
        $this->conn = getDBConnection();
    }
    
    // Secure query execution
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            // Instead of dying, return false so we can handle it
            error_log("SQL error: " . $this->conn->error . " | SQL: " . $sql);
            return false;
        }
        
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }
        
        if ($stmt->execute()) {
            return $stmt;
        } else {
            error_log("Execute error: " . $stmt->error);
            return false;
        }
    }
    
    // Get single row
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return ['count' => 0]; // Return default if query fails
        }
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Get multiple rows
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return []; // Return empty array if query fails
        }
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    // Check if table exists
    public function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $this->query($sql, [$tableName]);
        if ($stmt) {
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        }
        return false;
    }
    
    // Get last insert ID
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    public function close() {
        $this->conn->close();
    }

    // In Database class, add this method:
public function executeJSON($sql, $params = []) {
    $stmt = $this->query($sql, $params);
    if (!$stmt) {
        return ['success' => false, 'error' => $this->conn->error];
    }
    return ['success' => true, 'stmt' => $stmt];
}
}

?>