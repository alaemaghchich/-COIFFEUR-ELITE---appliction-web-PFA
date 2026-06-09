<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: /auth/login.php");
    exit();
}

include_once '../config/db.php';
include_once '../classes/Support.php';
include_once '../classes/Admin.php';

$database = new Database();
$db = $database->getConnection();
$supportObj = new Support($db);
$admin = new Admin($db);

$notifs = $admin->getNotificationCounts();

if(isset($_POST['resolve_id'])) {
    $supportObj->updateStatus($_POST['resolve_id'], 'resolved');
}

$requests = $supportObj->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Requests - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
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
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line me-2"></i> <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users me-2"></i> <span class="sidebar-text">Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="requests.php" class="nav-link">
                        <i class="fas fa-user-clock me-2"></i> <span class="sidebar-text">Barber Requests</span>
                        <?php if($notifs['pending_barbers'] > 0): ?><span class="notification-dot"></span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="support.php" class="nav-link active">
                        <i class="fas fa-headset me-2"></i> <span class="sidebar-text">Support</span>
                        <?php if($notifs['pending_support'] > 0): ?><span class="notification-dot"></span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="blacklist.php" class="nav-link">
                        <i class="fas fa-user-slash me-2"></i> <span class="sidebar-text">Blacklist</span>
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
            <header class="mb-5 d-flex align-items-center">
                <button class="sidebar-toggle me-3 d-lg-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h2 class="text-gold mb-0">Support & Complaints</h2>
            </header>

            
            <div class="card-luxury p-4">
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($requests as $r): ?>
                            <tr>
                                <td>
                                    <?php echo $r['name']; ?><br>
                                    <small class="text-gray-text"><?php echo $r['email']; ?></small>
                                </td>
                                <td>
                                    <span class="badge <?php echo $r['type'] == 'complaint' ? 'bg-danger' : 'bg-info'; ?>">
                                        <?php echo ucfirst($r['type']); ?>
                                    </span>
                                </td>
                                <td style="max-width: 300px;"><?php echo $r['message']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $r['status'] == 'pending' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                        <?php echo ucfirst($r['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($r['status'] == 'pending'): ?>
                                    <form action="support.php" method="POST">
                                        <input type="hidden" name="resolve_id" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-gold">Resolve</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
