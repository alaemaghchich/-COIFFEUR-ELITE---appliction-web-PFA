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

if(isset($_POST['delete_user'])) {
    $admin->deleteUser($_POST['user_id']);
}

$filters = [
    'name' => $_GET['name'] ?? '',
    'role' => $_GET['role'] ?? '',
    'city' => $_GET['city'] ?? ''
];

$users = $admin->getAllUsers($filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - BarberHub</title>
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
                <li class="nav-item"><a href="users.php" class="nav-link active"><i class="fas fa-users me-2"></i> Users</a></li>
                <li class="nav-item"><a href="requests.php" class="nav-link"><i class="fas fa-user-clock me-2"></i> Barber Requests</a></li>
                <li class="nav-item"><a href="blacklist.php" class="nav-link"><i class="fas fa-user-slash me-2"></i> Blacklist</a></li>
                <li class="nav-item mt-5"><a href="/auth/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-5">
            <header class="mb-5">
                <h2 class="mb-4">User Management</h2>
                
                <!-- Filters -->
                <form action="users.php" method="GET" class="row g-3 card-luxury p-4">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Search by name..." value="<?php echo $filters['name']; ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="barber" <?php if($filters['role'] == 'barber') echo 'selected'; ?>>Barber</option>
                            <option value="customer" <?php if($filters['role'] == 'customer') echo 'selected'; ?>>Customer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="city" class="form-control" placeholder="City" value="<?php echo $filters['city']; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gold w-100">Filter</button>
                    </div>
                </form>
            </header>

            <div class="card-luxury p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="bg-black text-gold">
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr class="align-middle">
                                <td class="ps-4">#<?php echo $u['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $u['profile_pic'] ? '/uploads/profiles/'.$u['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($u['full_name']); ?>" class="rounded-circle me-3" width="40" height="40">
                                        <div>
                                            <div class="fw-bold"><?php echo $u['full_name']; ?></div>
                                            <div class="text-gray-text small"><?php echo $u['email']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge <?php echo $u['role'] == 'barber' ? 'bg-primary' : 'bg-info'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                <td><?php echo $u['city']; ?></td>
                                <td>
                                    <?php if($u['status'] == 'active'): ?>
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                    <?php elseif($u['status'] == 'pending'): ?>
                                        <span class="text-warning"><i class="fas fa-clock me-1"></i> Pending</span>
                                    <?php else: ?>
                                        <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <form action="users.php" method="POST" onsubmit="return confirm('Delete this user permanently?');">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
