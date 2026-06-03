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
        $stmt->bindParam(":id", $customer_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function banIfNecessary($customer_id) {
        $count = $this->getNoShowCount($customer_id);
        if($count >= 5) {
            // Get user info
            $q = "SELECT email, phone FROM users WHERE id = :id";
            $s = $this->conn->prepare($q);
            $s->bindParam(":id", $customer_id);
            $s->execute();
            $user = $s->fetch(PDO::FETCH_ASSOC);

            // Add to blacklist
            $bq = "INSERT IGNORE INTO blacklist (email, phone, reason) VALUES (:email, :phone, '5 no-shows')";
            $bs = $this->conn->prepare($bq);
            $bs->bindParam(":email", $user['email']);
            $bs->bindParam(":phone", $user['phone']);
            $bs->execute();

            // Delete account
            $dq = "DELETE FROM users WHERE id = :id";
            $ds = $this->conn->prepare($dq);
            $ds->bindParam(":id", $customer_id);
            $ds->execute();

            return true;
        }
        return false;
    }
}
?>
