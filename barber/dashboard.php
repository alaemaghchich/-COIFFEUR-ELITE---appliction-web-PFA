<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'barber') {
    header("Location: /auth/login.php");
    exit();
}

include_once '../config/db.php';
include_once '../classes/Barber.php';
include_once '../classes/Booking.php';
include_once '../classes/Service.php';

$database = new Database();
$db = $database->getConnection();
$barber = new Barber($db);
$booking = new Booking($db);
$service = new Service($db);

$barber_id = $_SESSION['user_id'];
$details = $barber->getBarberDetails($barber_id);

if(isset($_POST['update_booking'])) {
    $booking->updateStatus($_POST['booking_id'], $barber_id, $_POST['status']);
    
    // Check for anti-spam if status is no-show
    if($_POST['status'] == 'no-show') {
        include_once '../classes/Customer.php';
        $custObj = new Customer($db);
        $custObj->banIfNecessary($_POST['customer_id']);
    }
}

$bookings = $booking->getBarberBookings($barber_id);
$services = $service->getByBarber($barber_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Dashboard - Coiffeur Elite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-darker">
    <nav class="navbar navbar-expand-lg navbar-luxury">
        <div class="container">
            <a class="navbar-brand text-gold fw-bold" href="#">COIFFEUR ELITE DASHBOARD</a>
            <div class="dropdown ms-auto">
                <a class="nav-link dropdown-toggle text-gold" href="#" data-bs-toggle="dropdown">
                    <img src="<?php echo $details['profile_pic'] ? '/uploads/profiles/'.$details['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($details['full_name']); ?>" class="rounded-circle me-2" width="30" height="30">
                    <?php echo $details['full_name']; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">Edit Profile</a></li>
                    <li><a class="dropdown-item" href="/customer/barber_view.php?id=<?php echo $barber_id; ?>">View Public Page</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2>Welcome, <?php echo $details['full_name']; ?></h2>
            <div class="d-flex gap-2">
                <a href="services.php" class="btn btn-outline-gold"><i class="fas fa-concierge-bell me-2"></i> Manage Services</a>
            </div>
        </div>

        <div class="row">
            <!-- Bookings Management -->
            <div class="col-12">
                <div class="card-luxury p-4">
                    <h4 class="text-gold mb-4"><i class="fas fa-calendar-alt me-2"></i> Upcoming Appointments</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Services</th>
                                    <th>Date & Time</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $b['customer_pic'] ? '/uploads/profiles/'.$b['customer_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($b['customer_name']); ?>" class="rounded-circle me-2" width="40" height="40">
                                            <div>
                                                <div><?php echo $b['customer_name']; ?></div>
                                                <div class="text-gray-text small"><?php echo $b['customer_phone']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php foreach($b['services'] as $sname): ?>
                                            <span class="badge bg-secondary me-1"><?php echo $sname; ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($b['booking_date'])); ?></div>
                                        <div class="text-gold small"><?php echo substr($b['start_time'], 0, 5); ?> - <?php echo substr($b['end_time'], 0, 5); ?></div>
                                    </td>
                                    <td class="text-gold fw-bold"><?php echo $b['total_price']; ?> MAD</td>
                                    <td>
                                        <?php 
                                        $statusClass = [
                                            'pending' => 'bg-warning text-dark',
                                            'accepted' => 'bg-info',
                                            'rejected' => 'bg-danger',
                                            'completed' => 'bg-success',
                                            'no-show' => 'bg-dark border border-secondary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $statusClass[$b['status']]; ?>">
                                            <?php echo ucfirst($b['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($b['status'] == 'pending'): ?>
                                            <form action="dashboard.php" method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-success">Accept</button>
                                            </form>
                                            <form action="dashboard.php" method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        <?php elseif($b['status'] == 'accepted'): ?>
                                            <form action="dashboard.php" method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $b['customer_id']; ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-gold">Completed</button>
                                            </form>
                                            <form action="dashboard.php" method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $b['customer_id']; ?>">
                                                <input type="hidden" name="status" value="no-show">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-outline-danger">No-Show</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($bookings)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-gray-text">No bookings found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
