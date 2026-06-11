<?php
include_once 'config/db.php';

try {
  $database = new Database();
    $db = $database->getConnection();

   // Configuration
    $email = "admin@coiffeurelite.com";
    $password = "Admin_00393690"; // You can change this
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->execute([":email" => $email]);

    if($stmt->rowCount() > 0) {
        $updateQuery = "UPDATE users SET password = :password, role = 'admin', status = 'active' WHERE email = :email";
       $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([":password" => $hashed_password, ":email" => $email]);
        echo "<h1>Admin Updated!</h1><p>Email: $email</p><p>Password: $password</p>";
    } else {
       $insertQuery = "INSERT INTO users (full_name, email, phone, password, role, gender, city, status) VALUES ('Main Admin', :email, '0600000000', :password, 'admin', 'male', 'Casablanca', 'active')";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([":email" => $email, ":password" => $hashed_password]);
        echo "<h1>Admin Created!</h1><p>Email: $email</p><p>Password: $password</p>";
    }
    echo "<br><b style='color:red'>PLEASE DELETE THIS FILE (restore_admin.php) AFTER RUNNING IT!</b>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

