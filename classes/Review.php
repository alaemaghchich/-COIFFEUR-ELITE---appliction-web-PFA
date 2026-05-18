<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByBarber($barber_id, $current_user_id = null) {
        $query = "SELECT r.*, u.full_name, u.profile_pic,
                  (SELECT COUNT(*) FROM review_likes WHERE review_id = r.id) as likes_count,
                  (SELECT COUNT(*) FROM review_likes WHERE review_id = r.id AND user_id = :current_user_id) as is_liked
                  FROM reviews r 
                  JOIN users u ON r.customer_id = u.id 
                  WHERE r.barber_id = :barber_id 
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->bindParam(":current_user_id", $current_user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $customer_id, $rating, $comment) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rating=:rating, comment=:comment 
                  WHERE id=:id AND customer_id=:customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":customer_id", $customer_id);
        return $stmt->execute();
    }

    public function delete($id, $customer_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id AND customer_id=:customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":customer_id", $customer_id);
        return $stmt->execute();
    }

    public function toggleLike($review_id, $user_id) {
        // Check if already liked
        $query = "SELECT * FROM review_likes WHERE review_id = :review_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":review_id", $review_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Unlike
            $query = "DELETE FROM review_likes WHERE review_id = :review_id AND user_id = :user_id";
        } else {
            // Like
            $query = "INSERT INTO review_likes (review_id, user_id) VALUES (:review_id, :user_id)";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":review_id", $review_id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
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
