<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByBarber($barber_id) {
        $query = "SELECT r.*, u.full_name, u.profile_pic 
                  FROM reviews r 
                  JOIN users u ON r.customer_id = u.id 
                  WHERE r.barber_id = :barber_id 
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($customer_id, $barber_id, $rating, $comment, $image = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET customer_id=:customer_id, barber_id=:barber_id, rating=:rating, 
                      comment=:comment, image=:image";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":image", $image);
        return $stmt->execute();
    }
}
?>
