<?php
class User {
    protected $conn;
    protected $table_name = "users";

    public $id;
    public $full_name;
    public $email;
    public $phone;
    public $password;
    public $role;
    public $gender;
    public $city;
    public $profile_pic;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($identifier, $password) {
        // Check if blacklisted first
        if($this->isBlacklisted($identifier, $identifier)) {
            return "This account or phone number is blacklisted.";
        }

        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE email = :email OR phone = :phone OR full_name = :name LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $identifier);
        $stmt->bindParam(":phone", $identifier);
        $stmt->bindParam(":name", $identifier);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                if($row['status'] == 'rejected') return "Your account has been rejected.";
                if($row['status'] == 'pending') return "Your account is pending approval.";
                
                $this->id = $row['id'];
                $this->full_name = $row['full_name'];
                $this->role = $row['role'];
                $this->status = $row['status'];
                
                // Update last login
                $updateQuery = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(":id", $this->id);
                $updateStmt->execute();

                return true;
            }
        }
        return false;
    }

    public function isBlacklisted($email, $phone) {
        $query = "SELECT id FROM blacklist WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register() {
        if($this->isBlacklisted($this->email, $this->phone)) {
            return "Your email or phone number is blacklisted.";
        }

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table_name . " 
                  SET full_name=:full_name, email=:email, phone=:phone, password=:password, 
                      role=:role, gender=:gender, city=:city, profile_pic=:profile_pic, status=:status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":profile_pic", $this->profile_pic);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
}
?>
