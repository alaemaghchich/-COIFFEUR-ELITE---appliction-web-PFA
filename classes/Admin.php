<?php
require_once 'User.php';

class Admin extends User {
    public function getAllUsers($filters = []) {
        $query = "SELECT * FROM users WHERE role != 'admin'";
        
        $params = [];
        if(!empty($filters['name'])) {
            $query .= " AND full_name LIKE :name";
            $params[':name'] = "%" . $filters['name'] . "%";
        }
        if(!empty($filters['role'])) {
            $query .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }
        if(!empty($filters['city'])) {
            $query .= " AND city = :city";
            $params[':city'] = $filters['city'];
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":id" => $id]);
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
        return $stmt->execute([":status" => $status, ":id" => $id]);
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
        return $stmt->execute([":id" => $id]);
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

    public function getRecentActivity() {
        $activities = [];

        // New Users (Barbers and Customers)
        $q1 = "SELECT full_name, role, created_at as date, status FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 5";
        $s1 = $this->conn->prepare($q1);
        $s1->execute();
        while($row = $s1->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = [
                'type' => 'registration',
                'name' => $row['full_name'],
                'role' => $row['role'],
                'date' => $row['date'],
                'status' => $row['status']
            ];
        }

        // New Bookings
        $q2 = "SELECT b.created_at as date, b.status, u.full_name as customer_name, b1.salon_name 
               FROM bookings b 
               JOIN users u ON b.customer_id = u.id 
               JOIN barber_details b1 ON b.barber_id = b1.user_id 
               ORDER BY b.created_at DESC LIMIT 5";
        $s2 = $this->conn->prepare($q2);
        $s2->execute();
        while($row = $s2->fetch(PDO::FETCH_ASSOC)) {
            $activities[] = [
                'type' => 'booking',
                'customer' => $row['customer_name'],
                'salon' => $row['salon_name'],
                'date' => $row['date'],
                'status' => $row['status']
            ];
        }

        // Sort all by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 7);
    }

    public function getNotificationCounts() {
        $counts = [];

        // Pending Barbers
        $q1 = "SELECT COUNT(*) as count FROM users WHERE role = 'barber' AND status = 'pending'";
        $s1 = $this->conn->prepare($q1);
        $s1->execute();
        $counts['pending_barbers'] = $s1->fetch(PDO::FETCH_ASSOC)['count'];

        // Pending Support
        $q2 = "SELECT COUNT(*) as count FROM support_requests WHERE status = 'pending'";
        $s2 = $this->conn->prepare($q2);
        $s2->execute();
        $counts['pending_support'] = $s2->fetch(PDO::FETCH_ASSOC)['count'];

        return $counts;
    }
}
?>
