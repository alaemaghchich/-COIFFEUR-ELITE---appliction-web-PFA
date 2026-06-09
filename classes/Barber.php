<?php
require_once 'User.php';

class Barber extends User {
    private $details_table = "barber_details";

    public $age;
    public $bio;
    public $experience_years;
    public $salon_name;
    public $salon_type;
    public $work_start;
    public $work_end;
    public $lat;
    public $lon;
    public $salon_logo;
    public $salon_img;
    public $diploma_or_video;

    public function registerBarber() {
        $this->role = 'barber';
        $this->status = 'pending';
        
        $res = $this->register();
        if($res === true) {
            $query = "INSERT INTO " . $this->details_table . " 
                      SET user_id=:user_id, age=:age, bio=:bio, experience_years=:experience_years, 
                          salon_name=:salon_name, salon_type=:salon_type, work_start=:work_start, 
                          work_end=:work_end, lat=:lat, lon=:lon, salon_logo=:salon_logo, 
                          salon_img=:salon_img, diploma_or_video=:diploma_or_video";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":user_id", $this->id);
            $stmt->bindParam(":age", $this->age);
            $stmt->bindParam(":bio", $this->bio);
            $stmt->bindParam(":experience_years", $this->experience_years);
            $stmt->bindParam(":salon_name", $this->salon_name);
            $stmt->bindParam(":salon_type", $this->salon_type);
            $stmt->bindParam(":work_start", $this->work_start);
            $stmt->bindParam(":work_end", $this->work_end);
            $stmt->bindParam(":lat", $this->lat);
            $stmt->bindParam(":lon", $this->lon);
            $stmt->bindParam(":salon_logo", $this->salon_logo);
            $stmt->bindParam(":salon_img", $this->salon_img);
            $stmt->bindParam(":diploma_or_video", $this->diploma_or_video);

            if($stmt->execute()) {
                return true;
            }
        }
        return $res;
    }

    public function updateProfile($data, $files = []) {
        try {
            $this->conn->beginTransaction();

            // 1. Update users table
            $user_query = "UPDATE users SET 
                           full_name = :full_name, 
                           email = :email, 
                           phone = :phone, 
                           city = :city, 
                           gender = :gender";
            
            if (!empty($data['password'])) {
                $user_query .= ", password = :password";
            }
            if (!empty($files['profile_pic'])) {
                $user_query .= ", profile_pic = :profile_pic";
            } elseif ($data['remove_profile_pic']) {
                $user_query .= ", profile_pic = NULL";
            }
            $user_query .= " WHERE id = :id";

            $stmt = $this->conn->prepare($user_query);
            $stmt->bindParam(":full_name", $data['full_name']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":phone", $data['phone']);
            $stmt->bindParam(":city", $data['city']);
            $stmt->bindParam(":gender", $data['gender']);
            $stmt->bindParam(":id", $data['user_id']);

            if (!empty($data['password'])) {
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt->bindParam(":password", $hashed_password);
            }
            if (!empty($files['profile_pic'])) {
                $stmt->bindParam(":profile_pic", $files['profile_pic']);
            }

            $stmt->execute();

            // 2. Update barber_details table
            $details_query = "UPDATE barber_details SET 
                              age = :age, 
                              bio = :bio, 
                              experience_years = :experience_years, 
                              salon_name = :salon_name, 
                              salon_type = :salon_type, 
                              work_start = :work_start, 
                              work_end = :work_end, 
                              lat = :lat, 
                              lon = :lon";
            
            if (!empty($files['salon_logo'])) {
                $details_query .= ", salon_logo = :salon_logo";
            } elseif ($data['remove_salon_logo']) {
                $details_query .= ", salon_logo = NULL";
            }

            if (!empty($files['salon_img'])) {
                $details_query .= ", salon_img = :salon_img";
            } elseif ($data['remove_salon_img']) {
                $details_query .= ", salon_img = NULL";
            }
            $details_query .= " WHERE user_id = :id";

            $stmt = $this->conn->prepare($details_query);
            $stmt->bindParam(":age", $data['age']);
            $stmt->bindParam(":bio", $data['bio']);
            $stmt->bindParam(":experience_years", $data['experience_years']);
            $stmt->bindParam(":salon_name", $data['salon_name']);
            $stmt->bindParam(":salon_type", $data['salon_type']);
            $stmt->bindParam(":work_start", $data['work_start']);
            $stmt->bindParam(":work_end", $data['work_end']);
            $stmt->bindParam(":lat", $data['lat']);
            $stmt->bindParam(":lon", $data['lon']);
            $stmt->bindParam(":id", $data['user_id']);

            if (!empty($files['salon_logo'])) {
                $stmt->bindParam(":salon_logo", $files['salon_logo']);
            }
            if (!empty($files['salon_img'])) {
                $stmt->bindParam(":salon_img", $files['salon_img']);
            }

            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    public function getBarberDetails($barber_id) {
        $query = "SELECT u.*, bd.* FROM users u 
                  JOIN barber_details bd ON u.id = bd.user_id 
                  WHERE u.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $barber_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchBarbers($filters = []) {
        $query = "SELECT u.id, u.full_name, u.city, u.profile_pic, bd.salon_name, bd.experience_years, bd.salon_type, bd.salon_logo, bd.salon_img 
                  FROM users u 
                  JOIN barber_details bd ON u.id = bd.user_id 
                  WHERE u.role = 'barber' AND u.status = 'active'";
        
        if(!empty($filters['query'])) {
            $query .= " AND (u.full_name LIKE :q OR bd.salon_name LIKE :q)";
        }
        if(!empty($filters['city'])) {
            $query .= " AND u.city = :city";
        }
        if(!empty($filters['type'])) {
            $query .= " AND bd.salon_type = :type";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if(!empty($filters['query'])) {
            $q = "%" . $filters['query'] . "%";
            $stmt->bindParam(":q", $q);
        }
        if(!empty($filters['city'])) {
            $stmt->bindParam(":city", $filters['city']);
        }
        if(!empty($filters['type'])) {
            $stmt->bindParam(":type", $filters['type']);
        }
        
        $stmt->execute();
        $barbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add rating for each (placeholder for now, will link to reviews)
        foreach($barbers as &$b) {
            $b['rating'] = $this->getAverageRating($b['id']);
        }

        return $barbers;
    }

    private function getAverageRating($barber_id) {
        $query = "SELECT AVG(rating) as avg FROM reviews WHERE barber_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $barber_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($row['avg'] ?: 0, 1);
    }
}
?>
