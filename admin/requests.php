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

if(isset($_POST['action'])) {
    $admin->updateBarberStatus($_POST['user_id'], $_POST['action']);
}

$requests = $admin->getPendingBarbers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Requests - Coiffeur Elite</title>
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
                    <a href="requests.php" class="nav-link active">
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
                <h2 class="mb-0">Requests</h2>
            </header>


            <?php if(empty($requests)): ?>
                <div class="card-luxury p-5 text-center">
                    <i class="fas fa-check-circle fa-4x text-gold mb-3"></i>
                    <h4>No pending requests</h4>
                    <p class="text-gray-text">All barber registrations have been processed.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($requests as $r): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card-luxury p-4 h-100">
                            <div class="d-flex align-items-center mb-4">
                                <img src="<?php echo $r['profile_pic'] ? '/uploads/profiles/'.$r['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($r['full_name']); ?>" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover;">
                                <div>
                                    <h4 class="text-gold mb-1 small text-uppercase"><?php echo $r['full_name']; ?></h4>
                                    <div class="text-gray-text small"><?php echo $r['salon_name']; ?></div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4 small">
                                <div class="col-6"><strong>Exp:</strong> <?php echo $r['experience_years']; ?>y</div>
                                <div class="col-6"><strong>City:</strong> <?php echo $r['city']; ?></div>
                                <div class="col-6"><strong>Type:</strong> <?php echo ucfirst($r['salon_type']); ?></div>
                                <div class="col-6"><strong>Hours:</strong> <?php echo date('H:i', strtotime($r['work_start'])); ?> - <?php echo date('H:i', strtotime($r['work_end'])); ?></div>
                            </div>

                            <p class="text-gray-text mb-4 small" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo $r['bio']; ?>
                            </p>

                            <div class="mb-4">
                                <a href="/uploads/diplomas/<?php echo $r['diploma_or_video']; ?>" target="_blank" class="btn btn-sm btn-outline-gold w-100">
                                    <i class="fas fa-file-alt me-2"></i> View Credentials
                                </a>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="requests.php" method="POST" class="flex-grow-1">
                                    <input type="hidden" name="user_id" value="<?php echo $r['user_id']; ?>">
                                    <input type="hidden" name="action" value="active">
                                    <button type="submit" class="btn btn-gold btn-sm w-100">Approve</button>
                                </form>
                                <form action="requests.php" method="POST" class="flex-grow-1">
                                    <input type="hidden" name="user_id" value="<?php echo $r['user_id']; ?>">
                                    <input type="hidden" name="action" value="rejected">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>


</body>
</html>
