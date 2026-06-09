<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: /auth/login.php");
    exit();
}

include_once '../config/db.php';
include_once '../classes/Admin.php';

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db);

$stats = $admin->getStats();
$activities = $admin->getRecentActivity();
$notifs = $admin->getNotificationCounts();

function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60 );
    $hours           = round($seconds / 3600);
    $days          = round($seconds / 86400);
    $weeks          = round($seconds / 604800);
    $months          = round($seconds / 2629440);
    $years          = round($seconds / 31553280);

    if($seconds <= 60) return "Just now";
    else if($minutes <= 60) return ($minutes==1) ? "1 min ago" : "$minutes mins ago";
    else if($hours <= 24) return ($hours==1) ? "1 hr ago" : "$hours hrs ago";
    else if($days <= 7) return ($days==1) ? "yesterday" : "$days days ago";
    else return date('M d, Y', $time_ago);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Coiffeur Elite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .stat-card { border-left: 5px solid var(--gold); }
    </style>
</head>
<body class="bg-darker">
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar p-4" id="sidebar">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <h3 class="text-gold mb-0 sidebar-text">COIFFEUR ELITE</h3>
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            </div>
            
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-chart-line me-2"></i> 
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users me-2"></i> 
                        <span class="sidebar-text">Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="requests.php" class="nav-link">
                        <i class="fas fa-user-clock me-2"></i> 
                        <span class="sidebar-text">Barber Requests</span>
                        <?php if($notifs['pending_barbers'] > 0): ?>
                            <span class="notification-dot"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="support.php" class="nav-link">
                        <i class="fas fa-headset me-2"></i> 
                        <span class="sidebar-text">Support</span>
                        <?php if($notifs['pending_support'] > 0): ?>
                            <span class="notification-dot"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="blacklist.php" class="nav-link">
                        <i class="fas fa-user-slash me-2"></i> 
                        <span class="sidebar-text">Blacklist</span>
                    </a>
                </li>
                
                <hr class="border-secondary my-4">
                
                <li class="nav-item">
                    <a href="/index.php" class="nav-link text-gold">
                        <i class="fas fa-home me-2"></i> 
                        <span class="sidebar-text">Main Site</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/auth/logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> 
                        <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content p-3 p-lg-5">
            <header class="mb-5 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="sidebar-toggle me-3 d-lg-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <h2 class="mb-0">Overview</h2>
                </div>
                <div class="text-gray-text d-none d-sm-block">Admin: <span class="text-gold"><?php echo $_SESSION['user_name']; ?></span></div>
            </header>

            <div class="row g-4 mb-5">
                <div class="col-6 col-lg-4">
                    <div class="card-luxury p-3 p-lg-4 stat-card">
                        <h6 class="text-gray-text text-uppercase small mb-2">Barbers</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><?php echo $stats['barbers']; ?></h2>
                            <i class="fas fa-cut fa-lg text-gold opacity-50 d-none d-md-block"></i>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4">
                    <div class="card-luxury p-3 p-lg-4 stat-card">
                        <h6 class="text-gray-text text-uppercase small mb-2">Customers</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><?php echo $stats['customers']; ?></h2>
                            <i class="fas fa-users fa-lg text-gold opacity-50 d-none d-md-block"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card-luxury p-3 p-lg-4 stat-card">
                        <h6 class="text-gray-text text-uppercase small mb-2">Bookings</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><?php echo $stats['bookings']; ?></h2>
                            <i class="fas fa-calendar-check fa-lg text-gold opacity-50 d-none d-md-block"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card-luxury p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Recent Platform Activity</h4>
                    <span class="badge bg-gold text-dark"><?php echo count($activities); ?> New</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Details</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($activities as $act): ?>
                            <tr>
                                <td>
                                    <?php if($act['type'] == 'registration'): ?>
                                        <i class="fas fa-user-plus text-info me-2"></i> New <?php echo ucfirst($act['role']); ?>
                                    <?php else: ?>
                                        <i class="fas fa-calendar-alt text-warning me-2"></i> New Appointment
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($act['type'] == 'registration'): ?>
                                        <span class="text-gold"><?php echo $act['name']; ?></span> joined the platform
                                    <?php else: ?>
                                        <span class="text-gold"><?php echo $act['customer']; ?></span> booked at <span class="text-gold"><?php echo $act['salon']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-gray-text small"><?php echo time_ago($act['date']); ?></td>
                                <td>
                                    <?php 
                                    $status_class = 'bg-secondary';
                                    if($act['status'] == 'active' || $act['status'] == 'accepted') $status_class = 'bg-success';
                                    if($act['status'] == 'pending') $status_class = 'bg-warning text-dark';
                                    if($act['status'] == 'rejected' || $act['status'] == 'no-show') $status_class = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($act['status']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($activities)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-text">No recent activity found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>

   
</body>
</html>
