<?php
class Support {
    private $conn;
    private $table_name = "support_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            type ENUM('complaint', 'support', 'other') NOT NULL,
            message TEXT NOT NULL,
            status ENUM('pending', 'resolved') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        return $this->conn->exec($query);
    }

    public function create($name, $email, $type, $message) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, type=:type, message=:message";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":message", $message);

        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
