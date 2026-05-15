<?php
class Service {
    private $conn;
    private $table_name = "services";

    public $id;
    public $barber_id;
    public $name;
    public $duration;
    public $price;
    public $image;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET barber_id=:barber_id, name=:name, duration=:duration, price=:price, image=:image";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":barber_id", $this->barber_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":duration", $this->duration);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);

        return $stmt->execute();
    }

    public function getByBarber($barber_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE barber_id = :barber_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":barber_id", $barber_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id, $barber_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND barber_id = :barber_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":barber_id", $barber_id);
        return $stmt->execute();
    }
}
?>
