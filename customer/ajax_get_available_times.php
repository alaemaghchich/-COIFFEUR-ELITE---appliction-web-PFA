<?php
ob_start();
header('Content-Type: application/json');

include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../classes/Barber.php';
include_once __DIR__ . '/../classes/Booking.php';

$barber_id = isset($_GET['barber_id']) ? (int) $_GET['barber_id'] : 0;
$date = $_GET['date'] ?? '';
$duration = isset($_GET['duration']) ? (int) $_GET['duration'] : 0;

if ($barber_id <= 0 || !$date || $duration <= 0) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    $barberObj = new Barber($db);
    $bookingObj = new Booking($db);

    $barber = $barberObj->getBarberDetails($barber_id);
    if (!$barber) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Barber not found']);
        exit;
    }

    if (empty($barber['work_start']) || empty($barber['work_end'])) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Work hours not set']);
        exit;
    }

    $workStart = strtotime($barber['work_start']);
    $workEnd = strtotime($barber['work_end']);
    $durationSec = $duration * 60;
    $slotStep = 15 * 60;
    $bookings = $bookingObj->getBookingsByDate($barber_id, $date);
    $isToday = ($date === date('Y-m-d'));
    $now = time();
    $times = [];

    for ($slot = $workStart; $slot + $durationSec <= $workEnd; $slot += $slotStep) {
        $slotTime = date('H:i:s', $slot);
        $slotEnd = $slot + $durationSec;

        if ($isToday && strtotime($date . ' ' . $slotTime) < $now) {
            continue;
        }

        $available = true;
        foreach ($bookings as $booking) {
            $bookingStart = strtotime($booking['start_time']);
            $bookingEnd = strtotime($booking['end_time']);
            if ($slot < $bookingEnd && $slotEnd > $bookingStart) {
                $available = false;
                break;
            }
        }

        if ($available) {
            $times[] = [
                'value' => $slotTime,
                'label' => date('H:i', $slot)
            ];
        }
    }

    ob_end_clean();
    echo json_encode(['status' => 'success', 'times' => $times]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
