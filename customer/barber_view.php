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
$reviews = $reviewObj->getByBarber($barber_id, $_SESSION['user_id'] ?? null);

$success = $_SESSION['success'] ?? "";
$error = $_SESSION['error'] ?? "";
unset($_SESSION['success'], $_SESSION['error']);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['book_now'])) {
        if(!isset($_SESSION['user_id'])) {
            header("Location: /auth/login.php");
            exit();
        }
        
        $customer_id = $_SESSION['user_id'];
        $date = $_POST['booking_date'];
        $start_time = $_POST['booking_time'];
        $selected_services = $_POST['services'] ?? [];
        
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
        
        $new_booking_id = $bookingObj->create($customer_id, $barber_id, $date, $start_time, $end_time, $total_price, $selected_services);
        if($new_booking_id) {
            $_SESSION['success'] = "Booking request sent successfully!";
            header("Location: booking_details.php?id=$new_booking_id");
            exit();
        } else {
            $_SESSION['error'] = "Booking failed. Please try again.";
        }
        header("Location: barber_view.php?id=$barber_id");
        exit();
    }

    if(isset($_POST['add_review'])) {
        if(!isset($_SESSION['user_id'])) {
            header("Location: /auth/login.php");
            exit();
        }

        $customer_id = $_SESSION['user_id'];
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];
        
        if($reviewObj->create($customer_id, $barber_id, $rating, $comment)) {
            $_SESSION['success'] = "Review added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add review.";
        }
        header("Location: barber_view.php?id=$barber_id");
        exit();
    }

    if(isset($_POST['update_review'])) {
        if(!isset($_SESSION['user_id'])) exit();
        $reviewObj->update($_POST['review_id'], $_SESSION['user_id'], $_POST['rating'], $_POST['comment']);
        header("Location: barber_view.php?id=$barber_id");
        exit();
    }

    if(isset($_POST['delete_review'])) {
        if(!isset($_SESSION['user_id'])) exit();
        $reviewObj->delete($_POST['review_id'], $_SESSION['user_id']);
        header("Location: barber_view.php?id=$barber_id");
        exit();
    }
}
?>

<div class="container py-4 py-lg-5">
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Barber Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="position-relative rounded-4 overflow-hidden shadow-lg" style="min-height: 250px;">
                <!-- Cover Image -->
                <div style="height: 350px; background: url('<?php echo $barber['salon_img'] ? '/uploads/salons/'.$barber['salon_img'] : 'https://images.unsplash.com/photo-1512690196236-d5a232ebbc74?auto=format&fit=crop&w=1200&q=80'; ?>') center/cover no-repeat;">
                    <div class="h-100 w-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.9));"></div>
                </div>
                
                <!-- Profile Info Overlay -->
                <div class="position-absolute bottom-0 start-0 w-100 p-3 p-md-4 p-lg-5">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-4">
                        <!-- Profile Pic & Salon Logo -->
                        <div class="position-relative">
                            <img src="<?php echo $barber['profile_pic'] ? '/uploads/profiles/'.$barber['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($barber['full_name']); ?>" class="rounded-circle border border-4 border-gold bg-black shadow-lg" width="140" height="140" style="object-fit: cover;">
                            <?php if($barber['salon_logo']): ?>
                                <img src="/uploads/salons/<?php echo $barber['salon_logo']; ?>" class="position-absolute rounded-circle border border-2 border-gold bg-black" style="width: 45px; height: 45px; bottom: 5px; right: 5px; object-fit: cover; z-index: 2;">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Text Info -->
                        <div class="text-center text-md-start flex-grow-1">
                            <h1 class="text-white mb-1 h2 fw-bold"><?php echo $barber['full_name']; ?></h1>
                            <p class="text-gold mb-2 lead small-caps tracking-wide"><?php echo $barber['salon_name']; ?></p>
                            <div class="d-flex justify-content-center justify-content-md-start align-items-center gap-3">
                                <span class="badge bg-gold text-dark fs-6"><i class="fas fa-star me-1"></i> <?php echo $barberObj->searchBarbers(['id' => $barber_id])[0]['rating'] ?? '5.0'; ?></span>
                                <span class="text-gray-text small"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $barber['city']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 g-lg-5">
        <!-- Main Content (About, Services, Reviews) -->
        <div class="col-lg-8">
            <!-- About -->
            <div class="card-luxury p-4 mb-4">
                <h4 class="text-gold mb-3 h5">Professional Profile</h4>
                <p class="text-gray-text mb-4"><?php echo $barber['bio']; ?></p>
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <small class="text-gray-text d-block">Experience</small>
                        <span class="fw-bold"><?php echo $barber['experience_years']; ?> Years</span>
                    </div>
                    <div class="col-6 col-md-4">
                        <small class="text-gray-text d-block">Operating Hours</small>
                        <span class="fw-bold"><?php echo substr($barber['work_start'], 0, 5); ?> - <?php echo substr($barber['work_end'], 0, 5); ?></span>
                    </div>
                    <div class="col-6 col-md-4">
                        <small class="text-gray-text d-block">Target Client</small>
                        <span class="fw-bold"><?php echo ucfirst($barber['salon_type']); ?></span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="https://www.google.com/maps?q=<?php echo $barber['lat']; ?>,<?php echo $barber['lon']; ?>" target="_blank" class="btn btn-outline-gold btn-sm w-100 w-md-auto">
                        <i class="fas fa-location-arrow me-2"></i> Directions via GPS
                    </a>
                </div>
            </div>

            <!-- Services -->
            <h3 class="mb-4 h4 text-uppercase">Elite Services</h3>
            <div class="row g-3 mb-5">
                <?php foreach($services as $s): ?>
                <div class="col-md-6">
                    <div class="card-luxury p-0 overflow-hidden d-flex h-100">
                        <img src="<?php echo $s['image'] ? '/uploads/services/'.$s['image'] : 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=200&q=80'; ?>" width="100" style="object-fit: cover;">
                        <div class="p-3 flex-grow-1">
                            <h6 class="text-white mb-1"><?php echo $s['name']; ?></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-gray-text small"><?php echo $s['duration']; ?> min</span>
                                <span class="text-gold fw-bold"><?php echo $s['price']; ?> MAD</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Reviews -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 h4 text-uppercase">Client Reviews</h3>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'customer'): ?>
                    <button class="btn btn-gold btn-sm" data-bs-toggle="collapse" data-bs-target="#reviewForm">
                        <i class="fas fa-feather-alt me-2"></i> Write
                    </button>
                <?php endif; ?>
            </div>

            <!-- Review Form -->
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'customer'): ?>
            <div class="collapse mb-4" id="reviewForm">
                <div class="card-luxury p-4">
                    <form action="barber_view.php?id=<?php echo $barber_id; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-gray-text small text-uppercase">Rating</label>
                            <div class="rating-input text-gold fs-4" style="cursor: pointer;">
                                <input type="hidden" name="rating" id="ratingValue" value="5">
                                <i class="fas fa-star star-btn" data-value="1"></i>
                                <i class="fas fa-star star-btn" data-value="2"></i>
                                <i class="fas fa-star star-btn" data-value="3"></i>
                                <i class="fas fa-star star-btn" data-value="4"></i>
                                <i class="fas fa-star star-btn" data-value="5"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-text small text-uppercase">Message</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="How was your experience?" required></textarea>
                        </div>
                        <button type="submit" name="add_review" class="btn btn-gold w-100">Post Review</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-luxury p-4 mb-5">
                <?php if(empty($reviews)): ?>
                    <p class="text-gray-text text-center py-4">Be the first to share your experience with <?php echo $barber['full_name']; ?>.</p>
                <?php else: ?>
                    <?php foreach($reviews as $r): ?>
                    <div class="mb-4 pb-4 border-bottom border-secondary last-no-border" id="review-<?php echo $r['id']; ?>">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $r['profile_pic'] ? '/uploads/profiles/'.$r['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($r['full_name']); ?>" class="rounded-circle me-3 border border-secondary" width="45" height="45">
                                <div>
                                    <h6 class="mb-0 text-white"><?php echo $r['full_name']; ?></h6>
                                    <small class="text-gray-text"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-gold small">
                                    <?php for($i=0; $i<$r['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                                </div>
                                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $r['customer_id']): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-gray-text p-0" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark">
                                            <li><a class="dropdown-item edit-review-btn" href="javascript:void(0)" data-id="<?php echo $r['id']; ?>" data-rating="<?php echo $r['rating']; ?>" data-comment="<?php echo htmlspecialchars($r['comment']); ?>">Edit</a></li>
                                            <li>
                                                <form action="barber_view.php?id=<?php echo $barber_id; ?>" method="POST" onsubmit="return confirm('Remove this review?')">
                                                    <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
                                                    <button type="submit" name="delete_review" class="dropdown-item text-danger">Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-gray-text review-comment mb-0 fst-italic">"<?php echo $r['comment']; ?>"</p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar (Booking) -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <div class="card-luxury p-4 shadow-lg border-gold">
                    <h4 class="text-gold mb-4 h5 text-uppercase tracking-wide">Instant Reservation</h4>
                    <form action="barber_view.php?id=<?php echo $barber_id; ?>" method="POST" id="bookingForm">
                        <div class="mb-4">
                            <label class="form-label text-gray-text small text-uppercase">Choose Services</label>
                            <div class="service-selection-container" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach($services as $s): ?>
                                <label class="service-item-custom mb-2" for="s<?php echo $s['id']; ?>">
                                    <input class="service-check" type="checkbox" name="services[]" value="<?php echo $s['id']; ?>" data-price="<?php echo $s['price']; ?>" data-duration="<?php echo $s['duration']; ?>" id="s<?php echo $s['id']; ?>" style="display:none">
                                    <div class="service-card p-2">
                                        <div class="service-info">
                                            <span class="service-name small fw-bold"><?php echo $s['name']; ?></span>
                                            <span class="service-details x-small text-gray-text"><?php echo $s['duration']; ?> min</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="service-price small text-gold"><?php echo $s['price']; ?> MAD</span>
                                            <div class="service-check-icon ms-2"></div>
                                        </div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-gray-text small text-uppercase">Preferred Date</label>
                            <input type="date" name="booking_date" id="bookingDate" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-gray-text small text-uppercase">Available Slots</label>
                            <select name="booking_time" id="bookingTime" class="form-select" required disabled>
                                <option value="">Select a date first</option>
                            </select>
                        </div>

                        <div class="bg-black p-3 rounded-3 mb-4 border border-secondary">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-text small">Est. Duration:</span>
                                <span id="totalDuration" class="text-light small fw-bold" data-value="0">0 min</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-text small">Investment:</span>
                                <span id="totalPrice" class="text-gold fw-bold">0 MAD</span>
                            </div>
                        </div>

                        <button type="submit" name="book_now" class="btn btn-gold w-100 py-3 fw-bold text-uppercase" id="bookBtn" disabled>Confirm Booking</button>
                        <p class="text-center text-gray-text x-small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i> Payment is handled at the salon.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Review Modal -->
<div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-gold small text-uppercase fw-bold">Update Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="barber_view.php?id=<?php echo $barber_id; ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="review_id" id="editReviewId">
                    <div class="mb-3">
                        <label class="form-label text-gray-text small text-uppercase">Rating</label>
                        <div class="rating-input-edit text-gold fs-4">
                            <input type="hidden" name="rating" id="editRatingValue">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star star-edit-btn" data-value="<?php echo $i; ?>" style="cursor: pointer;"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-gray-text small text-uppercase">Message</label>
                        <textarea name="comment" id="editComment" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_review" class="btn btn-sm btn-gold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.small-caps { font-variant: small-caps; }
.tracking-wide { letter-spacing: 1px; }
.x-small { font-size: 0.75rem; }
.last-no-border:last-child { border-bottom: none !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checks = document.querySelectorAll('.service-check');
    const durationSpan = document.getElementById('totalDuration');
    const priceSpan = document.getElementById('totalPrice');
    const bookBtn = document.getElementById('bookBtn');
    const bookingDate = document.getElementById('bookingDate');
    const bookingTime = document.getElementById('bookingTime');
    const barberId = <?php echo $barber_id; ?>;

    let lastDate = '';
    let lastDuration = 0;

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
        durationSpan.dataset.value = totalDuration;
        priceSpan.textContent = totalPrice + ' MAD';
        
        const date = bookingDate.value;
        const duration = totalDuration > 0 ? totalDuration : 15;

        if(date) {
            if(date !== lastDate || duration !== lastDuration) {
                fetchAvailableTimes(date, duration);
            }
        } else {
            bookingTime.disabled = true;
            bookingTime.innerHTML = '<option value="">Select a date first</option>';
            lastDate = '';
            lastDuration = 0;
        }

        updateButtonState(selectedCount);
    }

    function updateButtonState(selectedCount) {
        if (selectedCount === undefined) {
            selectedCount = Array.from(checks).filter(c => c.checked).length;
        }
        bookBtn.disabled = selectedCount === 0 || !bookingDate.value || !bookingTime.value;
    }

    function fetchAvailableTimes(date, duration) {
        lastDate = date;
        lastDuration = duration;

        bookingTime.disabled = true;
        bookingTime.innerHTML = '<option value="">Searching...</option>';

        fetch(`ajax_get_available_times.php?barber_id=${barberId}&date=${date}&duration=${duration}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    bookingTime.innerHTML = '<option value="">Select Time</option>';
                    if(data.times.length === 0) {
                        bookingTime.innerHTML = '<option value="">Fully Booked</option>';
                    } else {
                        data.times.forEach(time => {
                            const option = document.createElement('option');
                            option.value = time.value;
                            option.textContent = time.label;
                            bookingTime.appendChild(option);
                        });
                        bookingTime.disabled = false;
                    }
                }
                updateButtonState();
            })
            .catch(error => {
                console.error('Error:', error);
                bookingTime.innerHTML = '<option value="">Error</option>';
                updateButtonState();
            });
    }

    checks.forEach(check => {
        check.addEventListener('change', updateTotals);
    });

    bookingDate.addEventListener('change', updateTotals);
    bookingTime.addEventListener('change', () => updateButtonState());

    // Star Rating
    const stars = document.querySelectorAll('.star-btn');
    const ratingValue = document.getElementById('ratingValue');

    stars.forEach(star => {
        star.addEventListener('mouseover', function() { highlightStars(this.dataset.value); });
        star.addEventListener('mouseout', function() { highlightStars(ratingValue.value); });
        star.addEventListener('click', function() {
            ratingValue.value = this.dataset.value;
            highlightStars(this.dataset.value);
        });
    });

    function highlightStars(val) {
        stars.forEach(s => {
            s.classList.toggle('fas', s.dataset.value <= val);
            s.classList.toggle('far', s.dataset.value > val);
        });
    }

    highlightStars(5);

    // Edit Review
    const editModal = new bootstrap.Modal(document.getElementById('editReviewModal'));
    const editStars = document.querySelectorAll('.star-edit-btn');
    const editRatingValue = document.getElementById('editRatingValue');

    document.querySelectorAll('.edit-review-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editReviewId').value = this.dataset.id;
            editRatingValue.value = this.dataset.rating;
            document.getElementById('editComment').value = this.dataset.comment;
            updateEditStars(this.dataset.rating);
            editModal.show();
        });
    });

    editStars.forEach(star => {
        star.addEventListener('click', function() {
            editRatingValue.value = this.dataset.value;
            updateEditStars(this.dataset.value);
        });
    });

    function updateEditStars(val) {
        editStars.forEach(s => {
            s.classList.toggle('fas', s.dataset.value <= val);
            s.classList.toggle('far', s.dataset.value > val);
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>
