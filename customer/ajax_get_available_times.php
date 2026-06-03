<?php
header('Content-Type: application/json');

include_once '../config/db.php';
include_once '../classes/Barber.php';
include_once '../classes/Booking.php';

$barber_id = isset($_GET['barber_id']) ? (int) $_GET['barber_id'] : 0;
$date = $_GET['date'] ?? '';
$duration = isset($_GET['duration']) ? (int) $_GET['duration'] : 0;

if ($barber_id <= 0 || !$date || $duration <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$barberObj = new Barber($db);
$bookingObj = new Booking($db);

$barber = $barberObj->getBarberDetails($barber_id);
if (!$barber || empty($barber['work_start']) || empty($barber['work_end'])) {
    echo json_encode(['status' => 'success', 'times' => []]);
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

echo json_encode(['status' => 'success', 'times' => $times]);
