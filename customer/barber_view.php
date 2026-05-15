<?php
include_once '../includes/header.php';
include_once '../config/db.php';
include_once '../classes/Barber.php';
include_once '../classes/Service.php';
include_once '../classes/Review.php';
include_once '../classes/Booking.php';

$database = new Database();
$db = $database->getConnection();
$barberObj = new Barber($db);
$serviceObj = new Service($db);
$reviewObj = new Review($db);
$bookingObj = new Booking($db);

$barber_id = $_GET['id'] ?? null;
if(!$barber_id) {
    header("Location: search.php");
    exit();
}

$barber = $barberObj->getBarberDetails($barber_id);
$services = $serviceObj->getByBarber($barber_id);
$reviews = $reviewObj->getByBarber($barber_id);

$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_now'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: /auth/login.php");
        exit();
    }
    
    $customer_id = $_SESSION['user_id'];
    $date = $_POST['booking_date'];
    $start_time = $_POST['booking_time'];
    $selected_services = $_POST['services']; // Array of IDs
    
    // Calculate end time and total price
    $total_duration = 0;
    $total_price = 0;
    foreach($services as $s) {
        if(in_array($s['id'], $selected_services)) {
            $total_duration += $s['duration'];
            $total_price += $s['price'];
        }
    }
    
    $end_time = date('H:i:s', strtotime($start_time . " + $total_duration minutes"));
    
    if($bookingObj->create($customer_id, $barber_id, $date, $start_time, $end_time, $total_price, $selected_services)) {
        $success = "Booking request sent successfully!";
    } else {
        $error = "Booking failed. Please try again.";
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

    <!-- Barber Header -->
    <div class="row mb-5">
        <div class="col-12 position-relative">
            <div class="rounded-3 overflow-hidden" style="height: 350px; background: url('<?php echo $barber['salon_img'] ? '/uploads/salons/'.$barber['salon_img'] : 'https://images.unsplash.com/photo-1512690196236-d5a232ebbc74?auto=format&fit=crop&w=1200&q=80'; ?>') center/cover no-repeat;">
                <div class="h-100 w-100" style="background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.8));"></div>
            </div>
            <div class="position-absolute bottom-0 start-0 p-5 w-100 d-flex align-items-end">
                <img src="<?php echo $barber['profile_pic'] ? '/uploads/profiles/'.$barber['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($barber['full_name']); ?>" class="rounded-circle border border-4 border-gold me-4" width="120" height="120">
                <div class="mb-2 flex-grow-1">
                    <h1 class="text-white mb-0"><?php echo $barber['full_name']; ?></h1>
                    <p class="text-gold lead mb-0"><?php echo $barber['salon_name']; ?></p>
                </div>
                <div class="text-end">
                    <div class="badge bg-gold text-dark fs-5 mb-2"><i class="fas fa-star me-1"></i> <?php echo $barberObj->searchBarbers(['id' => $barber_id])[0]['rating'] ?? '5.0'; ?></div>
                    <div class="text-gray-text small"><?php echo $barber['city']; ?>, Morocco</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Details & Services -->
        <div class="col-lg-8">
            <div class="card-luxury p-4 mb-5">
                <h4 class="text-gold mb-3">About the Barber</h4>
                <p class="text-gray-text"><?php echo $barber['bio']; ?></p>
                <hr class="border-secondary my-4">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-gray-text d-block">Experience</small>
                        <span class="fw-bold"><?php echo $barber['experience_years']; ?> Years</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-gray-text d-block">Working Hours</small>
                        <span class="fw-bold"><?php echo substr($barber['work_start'], 0, 5); ?> - <?php echo substr($barber['work_end'], 0, 5); ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-gray-text d-block">Salon Type</small>
                        <span class="fw-bold"><?php echo ucfirst($barber['salon_type']); ?></span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="https://www.google.com/maps?q=<?php echo $barber['lat']; ?>,<?php echo $barber['lon']; ?>" target="_blank" class="btn btn-outline-gold btn-sm">
                        <i class="fas fa-map-marker-alt me-2"></i> Open in Google Maps
                    </a>
                </div>
            </div>

            <h3 class="mb-4">Services</h3>
            <div class="row g-4 mb-5">
                <?php foreach($services as $s): ?>
                <div class="col-md-6">
                    <div class="card-luxury p-0 overflow-hidden d-flex">
                        <img src="<?php echo $s['image'] ? '/uploads/services/'.$s['image'] : 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=200&q=80'; ?>" width="120" style="object-fit: cover;">
                        <div class="p-3 flex-grow-1">
                            <h5 class="text-white mb-1"><?php echo $s['name']; ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-gray-text small"><?php echo $s['duration']; ?> min</span>
                                <span class="text-gold fw-bold"><?php echo $s['price']; ?> MAD</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Reviews Section -->
            <h3 class="mb-4">Reviews</h3>
            <div class="card-luxury p-4">
                <?php if(empty($reviews)): ?>
                    <p class="text-gray-text text-center">No reviews yet. Be the first to review!</p>
                <?php else: ?>
                    <?php foreach($reviews as $r): ?>
                    <div class="mb-4 pb-4 border-bottom border-secondary last-no-border">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $r['profile_pic'] ? '/uploads/profiles/'.$r['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($r['full_name']); ?>" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <h6 class="mb-0"><?php echo $r['full_name']; ?></h6>
                                    <small class="text-gray-text"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                                </div>
                            </div>
                            <div class="text-gold">
                                <?php for($i=0; $i<$r['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-gray-text mb-0"><?php echo $r['comment']; ?></p>
                        <?php if($r['image']): ?>
                            <img src="/uploads/reviews/<?php echo $r['image']; ?>" class="mt-3 rounded shadow-sm" style="max-width: 200px;">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Booking Sidebar -->
        <div class="col-lg-4">
            <div class="card-luxury p-4 sticky-top" style="top: 100px;">
                <h4 class="text-gold mb-4">Book Your Appointment</h4>
                <form action="barber_view.php?id=<?php echo $barber_id; ?>" method="POST" id="bookingForm">
                    <div class="mb-4">
                        <label class="form-label text-gray-text">Select Services</label>
                        <div class="service-list">
                            <?php foreach($services as $s): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-check" type="checkbox" name="services[]" value="<?php echo $s['id']; ?>" data-price="<?php echo $s['price']; ?>" data-duration="<?php echo $s['duration']; ?>" id="s<?php echo $s['id']; ?>">
                                <label class="form-check-label text-light" for="s<?php echo $s['id']; ?>">
                                    <?php echo $s['name']; ?> <span class="text-gray-text small">(<?php echo $s['price']; ?> MAD)</span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-gray-text">Select Date</label>
                        <input type="date" name="booking_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-gray-text">Select Time</label>
                        <select name="booking_time" class="form-select" required>
                            <?php 
                            $start = strtotime($barber['work_start']);
                            $end = strtotime($barber['work_end']);
                            while($start < $end) {
                                echo '<option value="'.date('H:i', $start).'">'.date('H:i', $start).'</option>';
                                $start = strtotime('+30 minutes', $start);
                            }
                            ?>
                        </select>
                    </div>

                    <div class="bg-black p-3 rounded mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-text">Total Duration:</span>
                            <span id="totalDuration" class="text-light">0 min</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-text">Total Price:</span>
                            <span id="totalPrice" class="text-gold fw-bold">0 MAD</span>
                        </div>
                    </div>

                    <button type="submit" name="book_now" class="btn btn-gold w-100 py-3" id="bookBtn" disabled>Reserve Now</button>
                    <p class="text-center text-gray-text small mt-3">Payment will be done at the salon.</p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checks = document.querySelectorAll('.service-check');
    const durationSpan = document.getElementById('totalDuration');
    const priceSpan = document.getElementById('totalPrice');
    const bookBtn = document.getElementById('bookBtn');

    function updateTotals() {
        let totalDuration = 0;
        let totalPrice = 0;
        let selectedCount = 0;

        checks.forEach(check => {
            if(check.checked) {
                totalDuration += parseInt(check.dataset.duration);
                totalPrice += parseFloat(check.dataset.price);
                selectedCount++;
            }
        });

        durationSpan.textContent = totalDuration + ' min';
        priceSpan.textContent = totalPrice + ' MAD';
        bookBtn.disabled = selectedCount === 0;
    }

    checks.forEach(check => {
        check.addEventListener('change', updateTotals);
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
