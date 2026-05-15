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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BarberHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-sidebar { min-width: 250px; }
        .stat-card { border-left: 5px solid var(--gold); }
    </style>
</head>
<body class="bg-darker">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar admin-sidebar p-4">
            <h3 class="text-gold mb-5">BARBERHUB</h3>
            <ul class="nav flex-column gap-3">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active"><i class="fas fa-home me-2"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link"><i class="fas fa-users me-2"></i> Users</a>
                </li>
                <li class="nav-item">
                    <a href="requests.php" class="nav-link"><i class="fas fa-user-clock me-2"></i> Barber Requests</a>
                </li>
                <li class="nav-item">
                    <a href="blacklist.php" class="nav-link"><i class="fas fa-user-slash me-2"></i> Blacklist</a>
                </li>
                <li class="nav-item mt-5">
                    <a href="/auth/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-5">
            <header class="mb-5 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Platform Overview</h2>
                <div class="text-gray-text">Welcome, Administrator</div>
            </header>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card-luxury p-4 stat-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-gray-text text-uppercase mb-2">Total Barbers</h6>
                                <h2 class="mb-0"><?php echo $stats['barbers']; ?></h2>
                            </div>
                            <i class="fas fa-cut fa-2x text-gold opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-luxury p-4 stat-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-gray-text text-uppercase mb-2">Total Customers</h6>
                                <h2 class="mb-0"><?php echo $stats['customers']; ?></h2>
                            </div>
                            <i class="fas fa-users fa-2x text-gold opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-luxury p-4 stat-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-gray-text text-uppercase mb-2">Total Bookings</h6>
                                <h2 class="mb-0"><?php echo $stats['bookings']; ?></h2>
                            </div>
                            <i class="fas fa-calendar-check fa-2x text-gold opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Placeholder -->
            <div class="card-luxury p-4">
                <h4 class="mb-4">Recent Platform Activity</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>New Barber Registration: Ahmed Groom</td>
                                <td>Just now</td>
                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                            </tr>
                            <tr>
                                <td>Customer Booking: Luxury Fade</td>
                                <td>15 mins ago</td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
