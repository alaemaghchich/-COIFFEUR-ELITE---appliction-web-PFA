<?php
include_once '../includes/header.php';
include_once '../config/db.php';
include_once '../classes/Booking.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'customer') {
    header("Location: /auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$bookingObj = new Booking($db);

$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    if($bookingObj->cancelByCustomer($_POST['booking_id'], $_SESSION['user_id'])) {
        $success = "Appointment cancelled successfully.";
    } else {
        $error = "Failed to cancel appointment. It might already be completed or rejected.";
    }
}

$bookings = $bookingObj->getCustomerBookings($_SESSION['user_id']);

function getStatusBadge($status) {
    switch($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'accepted': return 'bg-success';
        case 'rejected': return 'bg-danger';
        case 'completed': return 'bg-info';
        case 'cancelled': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}
?>

<div class="container py-5">
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-gold mb-0">My Appointments</h2>
        <a href="search.php" class="btn btn-outline-gold btn-sm">
            <i class="fas fa-plus me-2"></i> Book New
        </a>
    </div>

    <?php if(empty($bookings)): ?>
        <div class="card-luxury p-5 text-center">
            <i class="fas fa-calendar-times fs-1 text-gold mb-3"></i>
            <h3>No appointments yet</h3>
            <p class="text-gray-text">You haven't booked any services yet. Explore our barbers to get started!</p>
            <a href="search.php" class="btn btn-gold mt-3">Explore Barbers</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($bookings as $b): ?>
            <div class="col-12">
                <div class="card-luxury p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                            <img src="<?php echo $b['barber_pic'] ? '/uploads/profiles/'.$b['barber_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($b['barber_name']); ?>" class="rounded-circle border border-2 border-gold" width="80" height="80" style="object-fit: cover;">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <h5 class="text-white mb-1"><?php echo $b['barber_name']; ?></h5>
                            <p class="text-gold small mb-1"><i class="fas fa-cut me-1"></i> <?php echo $b['salon_name']; ?></p>
                            <p class="text-gray-text small mb-0"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $b['city']; ?></p>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-flex align-items-center mb-1">
                                <i class="far fa-calendar text-gold me-2"></i>
                                <span class="text-light"><?php echo date('D, M d, Y', strtotime($b['booking_date'])); ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="far fa-clock text-gold me-2"></i>
                                <span class="text-light"><?php echo date('H:i', strtotime($b['start_time'])); ?> - <?php echo date('H:i', strtotime($b['end_time'])); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <div class="badge <?php echo getStatusBadge($b['status']); ?> mb-2 p-2 px-3">
                                <?php echo ucfirst($b['status']); ?>
                            </div>
                            <h5 class="text-gold mb-0"><?php echo $b['total_price']; ?> MAD</h5>
                            <small class="text-gray-text d-block mt-1 mb-2"><?php echo implode(', ', $b['services']); ?></small>
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="booking_details.php?id=<?php echo $b['id']; ?>" class="btn btn-outline-gold btn-sm px-3">View Details</a>
                                <?php if(in_array($b['status'], ['pending', 'accepted'])): ?>
                                <form action="my_bookings.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="btn btn-outline-danger btn-sm px-3">Cancel</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>
