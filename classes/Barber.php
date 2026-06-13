<?php
require_once __DIR__ . '/User.php';

class Barber extends User {
    private $details_table = "barber_details";

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
                      SET user_id=:user_id, bio=:bio, experience_years=:experience_years, 
                          salon_name=:salon_name, salon_type=:salon_type, work_start=:work_start, 
                          work_end=:work_end, lat=:lat, lon=:lon, salon_logo=:salon_logo, 
                          salon_img=:salon_img, diploma_or_video=:diploma_or_video";

            $stmt = $this->conn->prepare($query);

            if($stmt->execute([
                ":user_id" => $this->id,
                ":bio" => $this->bio,
                ":experience_years" => $this->experience_years,
                ":salon_name" => $this->salon_name,
                ":salon_type" => $this->salon_type,
                ":work_start" => $this->work_start,
                ":work_end" => $this->work_end,
                ":lat" => $this->lat,
                ":lon" => $this->lon,
                ":salon_logo" => $this->salon_logo,
                ":salon_img" => $this->salon_img,
                ":diploma_or_video" => $this->diploma_or_video
            ])) {
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
            
            $params = [
                ":full_name" => $data['full_name'],
                ":email" => $data['email'],
                ":phone" => $data['phone'],
                ":city" => $data['city'],
                ":gender" => $data['gender'],
                ":id" => $data['user_id']
            ];

            if (!empty($data['password'])) {
                $user_query .= ", password = :password";
                $params[":password"] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if (!empty($files['profile_pic'])) {
                $user_query .= ", profile_pic = :profile_pic";
                $params[":profile_pic"] = $files['profile_pic'];
            } elseif ($data['remove_profile_pic']) {
                $user_query .= ", profile_pic = NULL";
            }
            $user_query .= " WHERE id = :id";

            $stmt = $this->conn->prepare($user_query);
            $stmt->execute($params);

            // 2. Update barber_details table
            $details_query = "UPDATE barber_details SET 
                              bio = :bio, 
                              experience_years = :experience_years, 
                              salon_name = :salon_name, 
                              salon_type = :salon_type, 
                              work_start = :work_start, 
                              work_end = :work_end, 
                              lat = :lat, 
                              lon = :lon";
            
            $details_params = [
                ":bio" => $data['bio'],
                ":experience_years" => $data['experience_years'],
                ":salon_name" => $data['salon_name'],
                ":salon_type" => $data['salon_type'],
                ":work_start" => $data['work_start'],
                ":work_end" => $data['work_end'],
                ":lat" => $data['lat'],
                ":lon" => $data['lon'],
                ":id" => $data['user_id']
            ];

            if (!empty($files['salon_logo'])) {
                $details_query .= ", salon_logo = :salon_logo";
                $details_params[":salon_logo"] = $files['salon_logo'];
            } elseif ($data['remove_salon_logo']) {
                $details_query .= ", salon_logo = NULL";
            }

            if (!empty($files['salon_img'])) {
                $details_query .= ", salon_img = :salon_img";
                $details_params[":salon_img"] = $files['salon_img'];
            } elseif ($data['remove_salon_img']) {
                $details_query .= ", salon_img = NULL";
            }
            $details_query .= " WHERE user_id = :id";

            $stmt = $this->conn->prepare($details_query);
            $stmt->execute($details_params);

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
        $stmt->execute([":id" => $barber_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchBarbers($filters = []) {
        $query = "SELECT u.id, u.full_name, u.city, u.profile_pic, bd.salon_name, bd.experience_years, bd.salon_type, bd.salon_logo, bd.salon_img 
                  FROM users u 
                  JOIN barber_details bd ON u.id = bd.user_id 
                  WHERE u.role = 'barber' AND u.status = 'active'";
        
        $params = [];
        if(!empty($filters['query'])) {
            $query .= " AND (u.full_name LIKE :q OR bd.salon_name LIKE :q)";
            $params[':q'] = "%" . $filters['query'] . "%";
        }
        if(!empty($filters['city'])) {
            $query .= " AND u.city = :city";
            $params[':city'] = $filters['city'];
        }
        if(!empty($filters['type'])) {
            $query .= " AND bd.salon_type = :type";
            $params[':type'] = $filters['type'];
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $barbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add rating for each (placeholder for now, will link to reviews)
        foreach($barbers as &$b) {
            $b['rating'] = $this->getAverageRating($b['id']);
        }

        return $barbers;
    }

    public function getAverageRating($barber_id) {
        $query = "SELECT AVG(rating) as avg FROM reviews WHERE barber_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":id" => $barber_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($row['avg'] ?: 0, 1);
    }
}
?>
