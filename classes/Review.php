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
        $stmt->execute([":barber_id" => $barber_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $customer_id, $rating, $comment) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rating=:rating, comment=:comment 
                  WHERE id=:id AND customer_id=:customer_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":rating" => $rating,
            ":comment" => $comment,
            ":id" => $id,
            ":customer_id" => $customer_id
        ]);
    }

    public function delete($id, $customer_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id AND customer_id=:customer_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([":id" => $id, ":customer_id" => $customer_id]);
    }

    public function create($customer_id, $barber_id, $rating, $comment) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET customer_id=:customer_id, barber_id=:barber_id, rating=:rating, 
                      comment=:comment";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":customer_id" => $customer_id,
            ":barber_id" => $barber_id,
            ":rating" => $rating,
            ":comment" => $comment
        ]);
    }
}
?>
