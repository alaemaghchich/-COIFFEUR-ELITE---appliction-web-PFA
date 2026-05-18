<?php
include_once '../includes/header.php';
include_once '../config/db.php';
include_once '../classes/Booking.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'customer') {
    header("Location: /auth/login.php");
    exit();
}

$booking_id = $_GET['id'] ?? null;
if(!$booking_id) {
    header("Location: my_bookings.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$bookingObj = new Booking($db);

$booking = $bookingObj->getBookingDetails($booking_id, $_SESSION['user_id']);

if(!$booking) {
    header("Location: my_bookings.php");
    exit();
}

$status_steps = [
    'pending' => 1,
    'accepted' => 2,
    'completed' => 3
];

$current_step = $status_steps[$booking['status']] ?? 0;
if($booking['status'] == 'rejected') $current_step = -1;

function getStepClass($step, $current) {
    if($current == -1) return 'step-rejected';
    if($step < $current) return 'step-completed';
    if($step == $current) return 'step-active';
    return 'step-pending';
}
?>

<style>
    .status-timeline {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 3rem;
    }
    .status-timeline::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #333;
        z-index: 0;
    }
    .status-step {
        position: relative;
        z-index: 1;
        text-align: center;
        flex: 1;
    }
    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #1a1a1a;
        border: 2px solid #333;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        color: #666;
        transition: all 0.3s ease;
    }
    .step-active .step-icon {
        border-color: #d4af37;
        color: #d4af37;
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
    }
    .step-completed .step-icon {
        background: #d4af37;
        border-color: #d4af37;
        color: #000;
    }
    .step-rejected .step-icon {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }
    .step-label {
        font-size: 0.85rem;
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .step-active .step-label { color: #d4af37; }
    .step-completed .step-label { color: #fff; }
</style>

<div class="container py-5">
    <div class="mb-4">
        <a href="my_bookings.php" class="text-gold text-decoration-none small">
            <i class="fas fa-arrow-left me-2"></i> Back to My Appointments
        </a>
    </div>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="card-luxury p-5 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-5">
                    <div>
                        <h2 class="text-white mb-1">Booking #<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></h2>
                        <p class="text-gray-text mb-0">Booked on <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></p>
                    </div>
                    <div class="text-end">
                        <span class="badge <?php echo $booking['status'] == 'rejected' ? 'bg-danger' : 'bg-gold text-dark'; ?> px-3 py-2 fs-6">
                            <?php echo strtoupper($booking['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="status-timeline">
                    <div class="status-step <?php echo getStepClass(1, $current_step); ?>">
                        <div class="step-icon"><i class="fas fa-clock"></i></div>
                        <div class="step-label">Pending</div>
                    </div>
                    <div class="status-step <?php echo getStepClass(2, $current_step); ?>">
                        <div class="step-icon"><i class="fas fa-check"></i></div>
                        <div class="step-label">Accepted</div>
                    </div>
                    <div class="status-step <?php echo getStepClass(3, $current_step); ?>">
                        <div class="step-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="step-label">Completed</div>
                    </div>
                </div>

                <?php if($booking['status'] == 'rejected'): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-5">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    This booking was declined by the barber. Please try booking another time or another barber.
                </div>
                <?php endif; ?>

                <h4 class="text-gold mb-4">Appointment Details</h4>
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-black p-3 rounded-circle me-3">
                                <i class="far fa-calendar-alt text-gold"></i>
                            </div>
                            <div>
                                <small class="text-gray-text d-block">Date</small>
                                <span class="text-white fw-bold"><?php echo date('l, F d, Y', strtotime($booking['booking_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-black p-3 rounded-circle me-3">
                                <i class="far fa-clock text-gold"></i>
                            </div>
                            <div>
                                <small class="text-gray-text d-block">Time Range</small>
                                <span class="text-white fw-bold"><?php echo date('H:i', strtotime($booking['start_time'])); ?> - <?php echo date('H:i', strtotime($booking['end_time'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="text-gold mb-4">Services Reserved</h4>
                <div class="bg-black bg-opacity-50 rounded-3 p-4 mb-5">
                    <?php foreach($booking['services'] as $s): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 last-no-border pb-3 border-bottom border-secondary border-opacity-25">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $s['image'] ? '/uploads/services/'.$s['image'] : 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=50&q=80'; ?>" class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                            <div>
                                <h6 class="text-white mb-0"><?php echo $s['name']; ?></h6>
                                <small class="text-gray-text"><?php echo $s['duration']; ?> min</small>
                            </div>
                        </div>
                        <span class="text-gold fw-bold"><?php echo $s['price']; ?> MAD</span>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2">
                        <h5 class="text-white mb-0">Total Amount</h5>
                        <h4 class="text-gold mb-0"><?php echo $booking['total_price']; ?> MAD</h4>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-gray-text small">Payment is to be made at the salon after your service.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-luxury p-4 sticky-top" style="top: 100px;">
                <h4 class="text-gold mb-4">Barber Information</h4>
                <div class="text-center mb-4">
                    <img src="<?php echo $booking['barber_pic'] ? '/uploads/profiles/'.$booking['barber_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($booking['barber_name']); ?>" class="rounded-circle border border-3 border-gold mb-3" width="120" height="120" style="object-fit: cover;">
                    <h5 class="text-white mb-1"><?php echo $booking['barber_name']; ?></h5>
                    <p class="text-gold mb-0"><?php echo $booking['salon_name']; ?></p>
                </div>
                
                <hr class="border-secondary mb-4">
                
                <div class="mb-3">
                    <small class="text-gray-text d-block">Location</small>
                    <p class="text-white mb-0"><i class="fas fa-map-marker-alt text-gold me-2"></i> <?php echo $booking['city']; ?></p>
                </div>
                
                <div class="mb-4">
                    <small class="text-gray-text d-block">Contact</small>
                    <p class="text-white mb-0"><i class="fas fa-phone text-gold me-2"></i> <?php echo $booking['barber_phone']; ?></p>
                </div>

                <a href="https://www.google.com/maps?q=<?php echo $booking['lat']; ?>,<?php echo $booking['lon']; ?>" target="_blank" class="btn btn-outline-gold w-100 mb-3">
                    <i class="fas fa-directions me-2"></i> Get Directions
                </a>
                
                <a href="barber_view.php?id=<?php echo $booking['barber_id']; ?>" class="btn btn-gold w-100">
                    View Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
