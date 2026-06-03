<?php
require_once 'User.php';

class Admin extends User {
    public function getAllUsers($filters = []) {
        $query = "SELECT * FROM users WHERE role != 'admin'";
        
        if(!empty($filters['name'])) {
            $query .= " AND full_name LIKE :name";
        }
        if(!empty($filters['role'])) {
            $query .= " AND role = :role";
        }
        if(!empty($filters['city'])) {
            $query .= " AND city = :city";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if(!empty($filters['name'])) {
            $name = "%" . $filters['name'] . "%";
            $stmt->bindParam(":name", $name);
        }
        if(!empty($filters['role'])) {
            $stmt->bindParam(":role", $filters['role']);
        }
        if(!empty($filters['city'])) {
            $stmt->bindParam(":city", $filters['city']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getPendingBarbers() {
        $query = "SELECT u.*, bd.* FROM users u 
                  JOIN barber_details bd ON u.id = bd.user_id 
                  WHERE u.status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateBarberStatus($id, $status) {
        $query = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getBlacklist() {
        $query = "SELECT * FROM blacklist ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeFromBlacklist($id) {
        $query = "DELETE FROM blacklist WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getStats() {
        $stats = [];
        
        $q1 = "SELECT COUNT(*) as count FROM users WHERE role = 'barber'";
        $s1 = $this->conn->prepare($q1);
        $s1->execute();
        $stats['barbers'] = $s1->fetch(PDO::FETCH_ASSOC)['count'];

        $q2 = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
        $s2 = $this->conn->prepare($q2);
        $s2->execute();
        $stats['customers'] = $s2->fetch(PDO::FETCH_ASSOC)['count'];

        $q3 = "SELECT COUNT(*) as count FROM bookings";
        $s3 = $this->conn->prepare($q3);
        $s3->execute();
        $stats['bookings'] = $s3->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }
}
?>
