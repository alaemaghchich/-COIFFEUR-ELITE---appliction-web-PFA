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

$notifs = $admin->getNotificationCounts();

if(isset($_POST['remove_blacklist'])) {
    if($admin->removeFromBlacklist($_POST['blacklist_id'])) {
        $_SESSION['success'] = "Email/Phone removed from blacklist.";
    } else {
        $_SESSION['error'] = "Failed to remove from blacklist.";
    }
    header("Location: blacklist.php");
    exit();
}

$blacklist = $admin->getBlacklist();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blacklist - Coiffeur Elite</title>
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
                    <a href="support.php" class="nav-link">
                        <i class="fas fa-headset me-2"></i> <span class="sidebar-text">Support</span>
                        <?php if($notifs['pending_support'] > 0): ?><span class="notification-dot"></span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="blacklist.php" class="nav-link active">
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
                <h2 class="mb-0">Blacklisted Accounts</h2>
            </header>


            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card-luxury p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="bg-black text-gold">
                            <tr>
                                <th class="ps-4 py-3">Email</th>
                                <th>Phone</th>
                                <th>Reason</th>
                                <th>Banned Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($blacklist as $b): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo $b['email'] ?: 'N/A'; ?></td>
                                <td><?php echo $b['phone']; ?></td>
                                <td><?php echo $b['reason']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($b['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <form action="blacklist.php" method="POST" onsubmit="return confirm('Allow this user to register again?')">
                                        <input type="hidden" name="blacklist_id" value="<?php echo $b['id']; ?>">
                                        <button type="submit" name="remove_blacklist" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-trash-restore me-1"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($blacklist)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-gray-text">No blacklisted accounts.</td>
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
