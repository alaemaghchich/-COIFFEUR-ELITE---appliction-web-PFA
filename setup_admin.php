<?php
include_once 'config/db.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $email = "admin@barberhub.com";
    $password = "Admin_00393690";

    // Check if admin exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        // Update existing admin
        $updateQuery = "UPDATE users SET password = :password WHERE email = :email";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":password", $password);
        $updateStmt->bindParam(":email", $email);
        $updateStmt->execute();
        echo "Admin password updated successfully!";
    } else {
        // Create new admin
        $insertQuery = "INSERT INTO users (full_name, email, phone, password, role, gender, city, status) 
                        VALUES ('Main Admin', :email, '0600000000', :password, 'admin', 'male', 'Casablanca', 'active')";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(":email", $email);
        $insertStmt->bindParam(":password", $password);
        $insertStmt->execute();
        echo "Admin account created successfully!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
