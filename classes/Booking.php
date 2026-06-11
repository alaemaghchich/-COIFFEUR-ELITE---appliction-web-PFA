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
        if($stmt->execute([
            ":customer_id" => $customer_id,
            ":barber_id" => $barber_id,
            ":date" => $date,
            ":start_time" => $start_time,
            ":end_time" => $end_time,
            ":total_price" => $total_price
        ])) {
            $booking_id = $this->conn->lastInsertId();
            foreach($service_ids as $sid) {
                $q = "INSERT INTO booking_services (booking_id, service_id) VALUES (:bid, :sid)";
                $s = $this->conn->prepare($q);
                $s->execute([":bid" => $booking_id, ":sid" => $sid]);
            }
            return $booking_id;
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
        $stmt->execute([":barber_id" => $barber_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get services for each booking
        foreach($bookings as &$b) {
            $q = "SELECT s.name FROM services s 
                  JOIN booking_services bs ON s.id = bs.service_id 
                  WHERE bs.booking_id = :bid";
            $s = $this->conn->prepare($q);
            $s->execute([":bid" => $b['id']]);
            $b['services'] = $s->fetchAll(PDO::FETCH_COLUMN);
        }
        return $bookings;
    }

    public function getBookingsByDate($barber_id, $date) {
        $query = "SELECT start_time, end_time FROM " . $this->table_name . " 
                  WHERE barber_id = :barber_id AND booking_date = :date 
                  AND status IN ('pending', 'accepted')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":barber_id" => $barber_id, ":date" => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function autoAccept($barber_id) {
        // Rule: If less than 1 hour remains until the booking time and status is 'pending', set to 'accepted'
        $query = "UPDATE bookings 
                  SET status = 'accepted' 
                  WHERE barber_id = :barber_id 
                  AND status = 'pending' 
                  AND TIMESTAMP(booking_date, start_time) <= DATE_ADD(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":barber_id" => $barber_id]);
    }

    public function updateStatus($booking_id, $barber_id, $status) {
        $query = "UPDATE bookings SET status = :status WHERE id = :id AND barber_id = :barber_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":status" => $status, ":id" => $booking_id, ":barber_id" => $barber_id]);
    }

    public function cancelByCustomer($booking_id, $customer_id) {
        // Can only cancel if status is 'pending' or 'accepted'
        $query = "UPDATE bookings SET status = 'cancelled' 
                  WHERE id = :id AND customer_id = :customer_id AND status IN ('pending', 'accepted')";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":id" => $booking_id, ":customer_id" => $customer_id]);
    }

    public function getCustomerBookings($customer_id) {
        $query = "SELECT b.*, u.full_name as barber_name, u.phone as barber_phone, u.profile_pic as barber_pic, u.city,
                  bd.salon_name 
                  FROM bookings b 
                  JOIN users u ON b.barber_id = u.id 
                  JOIN barber_details bd ON b.barber_id = bd.user_id
                  WHERE b.customer_id = :customer_id 
                  ORDER BY b.booking_date DESC, b.start_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":customer_id" => $customer_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($bookings as &$b) {
            $q = "SELECT s.name FROM services s 
                  JOIN booking_services bs ON s.id = bs.service_id 
                  WHERE bs.booking_id = :bid";
            $s = $this->conn->prepare($q);
            $s->execute([":bid" => $b['id']]);
            $b['services'] = $s->fetchAll(PDO::FETCH_COLUMN);
        }
        return $bookings;
    }

    public function getBookingDetails($booking_id, $customer_id) {
        $query = "SELECT b.*, u.full_name as barber_name, u.phone as barber_phone, u.profile_pic as barber_pic, u.city,
                  bd.salon_name, bd.salon_img, bd.lat, bd.lon
                  FROM bookings b 
                  JOIN users u ON b.barber_id = u.id 
                  JOIN barber_details bd ON b.barber_id = bd.user_id
                  WHERE b.id = :id AND b.customer_id = :customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":id" => $booking_id, ":customer_id" => $customer_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if($booking) {
            $q = "SELECT s.* FROM services s 
                  JOIN booking_services bs ON s.id = bs.service_id 
                  WHERE bs.booking_id = :bid";
            $s = $this->conn->prepare($q);
            $s->execute([":bid" => $booking['id']]);
            $booking['services'] = $s->fetchAll(PDO::FETCH_ASSOC);
        }
        return $booking;
    }
}
