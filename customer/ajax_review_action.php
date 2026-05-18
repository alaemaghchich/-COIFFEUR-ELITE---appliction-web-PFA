<?php
session_start();
include_once '../config/db.php';
include_once '../classes/Review.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$reviewObj = new Review($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_like') {
    $review_id = $_POST['review_id'];
    $user_id = $_SESSION['user_id'];

    if ($reviewObj->toggleLike($review_id, $user_id)) {
        // Get updated count and status
        $query = "SELECT 
                  (SELECT COUNT(*) FROM review_likes WHERE review_id = :review_id) as likes_count,
                  (SELECT COUNT(*) FROM review_likes WHERE review_id = :review_id AND user_id = :user_id) as is_liked";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":review_id", $review_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'likes_count' => $result['likes_count'],
            'is_liked' => $result['is_liked'] > 0
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>