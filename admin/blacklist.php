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

$blacklist = $admin->getBlacklist();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blacklist - BarberHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-darker">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar admin-sidebar p-4" style="min-width: 250px;">
            <h3 class="text-gold mb-5">BARBERHUB</h3>
            <ul class="nav flex-column gap-3">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i> Home</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users me-2"></i> Users</a></li>
                <li class="nav-item"><a href="requests.php" class="nav-link"><i class="fas fa-user-clock me-2"></i> Barber Requests</a></li>
                <li class="nav-item"><a href="blacklist.php" class="nav-link active"><i class="fas fa-user-slash me-2"></i> Blacklist</a></li>
                <li class="nav-item mt-5"><a href="/auth/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-5">
            <h2 class="mb-5">Blacklisted Accounts</h2>

            <div class="card-luxury p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="bg-black text-gold">
                            <tr>
                                <th class="ps-4 py-3">Email</th>
                                <th>Reason</th>
                                <th>Banned Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($blacklist as $b): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo $b['email']; ?></td>
                                <td><?php echo $b['reason']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($b['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($blacklist)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-gray-text">No blacklisted accounts.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
