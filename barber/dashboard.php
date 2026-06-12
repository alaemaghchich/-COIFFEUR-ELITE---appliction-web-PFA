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
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar p-4" id="sidebar">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <h3 class="text-gold mb-0 sidebar-text">BARBER</h3>
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            </div>
            
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-chart-line me-2"></i> <span class="sidebar-text">Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="services.php" class="nav-link">
                        <i class="fas fa-concierge-bell me-2"></i> <span class="sidebar-text">Services</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-edit me-2"></i> <span class="sidebar-text">My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/customer/barber_view.php?id=<?php echo $barber_id; ?>" class="nav-link text-info">
                        <i class="fas fa-eye me-2"></i> <span class="sidebar-text">Public View</span>
                    </a>
                </li>
                <hr class="border-secondary my-4">
                <li class="nav-item">
                    <a href="/index.php" class="nav-link text-gold">
                        <i class="fas fa-home me-2"></i> <span class="sidebar-text">Main Site</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/auth/logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content p-3 p-lg-5">
            <header class="mb-5 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="sidebar-toggle me-3 d-lg-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <h2 class="mb-0">Dashboard</h2>
                </div>
                <div class="text-gray-text d-none d-sm-block">
                    <img src="<?php echo $details['profile_pic'] ? '/uploads/profiles/'.$details['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($details['full_name']); ?>" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;">
                    <span class="text-gold"><?php echo $details['full_name']; ?></span>
                </div>
            </header>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-gold mb-0"><i class="fas fa-calendar-check me-2"></i> Appointments</h4>
                <div class="badge bg-gold text-white"><?php echo count($bookings); ?> Total</div>
            </div>

            <div class="card-luxury p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead class="bg-black text-gold">
                            <tr>
                                <th class="ps-4">Customer</th>
                                <th>Services</th>
                                <th>Schedule</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $b['customer_pic'] ? '/uploads/profiles/'.$b['customer_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($b['customer_name']); ?>" class="rounded-circle me-3" width="45" height="45" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo $b['customer_name']; ?></div>
                                            <div class="text-gray-text small"><?php echo $b['customer_phone']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach($b['services'] as $sname): ?>
                                            <span class="badge bg-secondary small"><?php echo $sname; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo date('M d, Y', strtotime($b['booking_date'])); ?></div>
                                    <div class="text-gold small"><?php echo date('H:i', strtotime($b['start_time'])); ?> - <?php echo date('H:i', strtotime($b['end_time'])); ?></div>
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
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <?php if($b['status'] == 'pending'): ?>
                                            <form action="dashboard.php" method="POST">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-success" title="Accept"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form action="dashboard.php" method="POST">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                                            </form>
                                        <?php elseif($b['status'] == 'accepted'): ?>
                                            <form action="dashboard.php" method="POST">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $b['customer_id']; ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-gold" title="Mark Completed"><i class="fas fa-check-double"></i></button>
                                            </form>
                                            <form action="dashboard.php" method="POST">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $b['customer_id']; ?>">
                                                <input type="hidden" name="status" value="no-show">
                                                <button type="submit" name="update_booking" class="btn btn-sm btn-outline-danger" title="No-Show"><i class="fas fa-user-slash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-gray-text">No upcoming appointments.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isMobile = window.innerWidth <= 992;
            
            if (isMobile) {
                sidebar.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('barberSidebarCollapsed', sidebar.classList.contains('collapsed'));
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = localStorage.getItem('barberSidebarCollapsed') === 'true';
            if (isCollapsed && window.innerWidth > 992) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
</body>
</html>
