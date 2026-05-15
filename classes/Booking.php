<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($customer_id, $barber_id, $date, $start_time, $end_time, $total_price, $service_ids) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET customer_id=:customer_id, barber_id=:barber_id, booking_date=:date, 
                      start_time=:start_time, end_time=:end_time, total_price=:total_price, status='pending'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":start_time", $start_time);
        $stmt->bindParam(":end_time", $end_time);
        $stmt->bindParam(":total_price", $total_price);

        if($stmt->execute()) {
            $booking_id = $this->conn->lastInsertId();
            foreach($service_ids as $sid) {
                $q = "INSERT INTO booking_services (booking_id, service_id) VALUES (:bid, :sid)";
                $s = $this->conn->prepare($q);
                $s->bindParam(":bid", $booking_id);
                $s->bindParam(":sid", $sid);
                $s->execute();
            }
            return true;
        }
        return false;
    }

    public function getBarberBookings($barber_id) {
        // Auto-accept bookings if less than 1 hour left and still pending
        $this->autoAccept($barber_id);

        $query = "SELECT b.*, u.full_name as customer_name, u.phone as customer_phone, u.profile_pic as customer_pic 
                  FROM bookings b 
                  JOIN users u ON b.customer_id = u.id 
                  WHERE b.barber_id = :barber_id 
                  ORDER BY b.booking_date ASC, b.start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get services for each booking
        foreach($bookings as &$b) {
            $q = "SELECT s.name FROM services s 
                  JOIN booking_services bs ON s.id = bs.service_id 
                  WHERE bs.booking_id = :bid";
            $s = $this->conn->prepare($q);
            $s->bindParam(":bid", $b['id']);
            $s->execute();
            $b['services'] = $s->fetchAll(PDO::FETCH_COLUMN);
        }
        return $bookings;
    }

    private function autoAccept($barber_id) {
        // Rule: If less than 1 hour remains until the booking time and status is 'pending', set to 'accepted'
        $query = "UPDATE bookings 
                  SET status = 'accepted' 
                  WHERE barber_id = :barber_id 
                  AND status = 'pending' 
                  AND TIMESTAMP(booking_date, start_time) <= DATE_ADD(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->execute();
    }

    public function updateStatus($booking_id, $barber_id, $status) {
        $query = "UPDATE bookings SET status = :status WHERE id = :id AND barber_id = :barber_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $booking_id);
        $stmt->bindParam(":barber_id", $barber_id);
        return $stmt->execute();
    }
}
?>
