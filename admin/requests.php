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
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar admin-sidebar p-4" style="min-width: 250px;">
            <h3 class="text-gold mb-5">COIFFEUR ELITE</h3>
            <ul class="nav flex-column gap-3">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users me-2"></i> Users</a></li>
                <li class="nav-item"><a href="requests.php" class="nav-link active"><i class="fas fa-user-clock me-2"></i> Barber Requests</a></li>
                <li class="nav-item"><a href="support.php" class="nav-link"><i class="fas fa-headset me-2"></i> Support</a></li>
                <li class="nav-item"><a href="blacklist.php" class="nav-link"><i class="fas fa-user-slash me-2"></i> Blacklist</a></li>
                <li class="nav-item mt-4"><a href="/index.php" class="nav-link text-gold"><i class="fas fa-home me-2"></i> Main Site</a></li>
                <li class="nav-item mt-2"><a href="/auth/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-5">
            <h2 class="mb-5">Pending Barber Requests</h2>

            <?php if(empty($requests)): ?>
                <div class="card-luxury p-5 text-center">
                    <i class="fas fa-check-circle fa-4x text-gold mb-3"></i>
                    <h4>No pending requests</h4>
                    <p class="text-gray-text">All barber registrations have been processed.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($requests as $r): ?>
                    <div class="col-md-6">
                        <div class="card-luxury p-4 h-100">
                            <div class="d-flex align-items-center mb-4">
                                <img src="<?php echo $r['profile_pic'] ? '/uploads/profiles/'.$r['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($r['full_name']); ?>" class="rounded-circle me-3" width="60" height="60">
                                <div>
                                    <h4 class="text-gold mb-1"><?php echo $r['full_name']; ?></h4>
                                    <div class="text-gray-text small"><?php echo $r['salon_name']; ?> (<?php echo ucfirst($r['salon_type']); ?>)</div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4 small">
                                <div class="col-6"><strong>Experience:</strong> <?php echo $r['experience_years']; ?> Years</div>
                                <div class="col-6"><strong>City:</strong> <?php echo $r['city']; ?></div>
                                <div class="col-6"><strong>Age:</strong> <?php echo $r['age']; ?></div>
                                <div class="col-6"><strong>Hours:</strong> <?php echo $r['work_start']; ?> - <?php echo $r['work_end']; ?></div>
                            </div>

                            <p class="text-gray-text mb-4"><?php echo $r['bio']; ?></p>

                            <div class="mb-4">
                                <h6>Verification Documents:</h6>
                                <a href="/uploads/diplomas/<?php echo $r['diploma_or_video']; ?>" target="_blank" class="btn btn-sm btn-outline-gold">
                                    <i class="fas fa-file-alt me-2"></i> View Diploma/Video
                                </a>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="requests.php" method="POST" class="flex-grow-1">
                                    <input type="hidden" name="user_id" value="<?php echo $r['user_id']; ?>">
                                    <input type="hidden" name="action" value="active">
                                    <button type="submit" class="btn btn-gold w-100">Approve</button>
                                </form>
                                <form action="requests.php" method="POST" class="flex-grow-1">
                                    <input type="hidden" name="user_id" value="<?php echo $r['user_id']; ?>">
                                    <input type="hidden" name="action" value="rejected">
                                    <button type="submit" class="btn btn-outline-danger w-100">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
