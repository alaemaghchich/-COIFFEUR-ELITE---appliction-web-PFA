<?php
require_once 'User.php';

class Customer extends User {
    public function registerCustomer() {
        $this->role = 'customer';
        $this->status = 'active';
        return $this->register();
    }

    public function getNoShowCount($customer_id) {
        $query = "SELECT COUNT(*) as count FROM bookings WHERE customer_id = :id AND status = 'no-show'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":id" => $customer_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function banIfNecessary($customer_id) {
        $count = $this->getNoShowCount($customer_id);
        if($count >= 5) {
            // Get user info
            $q = "SELECT email, phone FROM users WHERE id = :id";
            $s = $this->conn->prepare($q);
            $s->execute([":id" => $customer_id]);
            $user = $s->fetch(PDO::FETCH_ASSOC);

            // Add to blacklist (Note: database schema only has email, so we skip phone if not in schema)
            $bq = "INSERT IGNORE INTO blacklist (email, reason) VALUES (:email, '5 no-shows')";
            $bs = $this->conn->prepare($bq);
            $bs->execute([":email" => $user['email']]);

            // Delete account
            $dq = "DELETE FROM users WHERE id = :id";
            $ds = $this->conn->prepare($dq);
            $ds->execute([":id" => $customer_id]);

            return true;
        }
        return false;
    }
}
?>
