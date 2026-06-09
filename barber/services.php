<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'barber') {
    header("Location: /auth/login.php");
    exit();
}

include_once '../config/db.php';
include_once '../classes/Service.php';

$database = new Database();
$db = $database->getConnection();
$service = new Service($db);

$barber_id = $_SESSION['user_id'];

if(isset($_POST['add_service'])) {
    $service->barber_id = $barber_id;
    $service->name = $_POST['name'];
    $service->duration = $_POST['duration'];
    $service->price = $_POST['price'];
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/services/" . $img_name);
        $service->image = $img_name;
    }
    
    $service->create();
}

if(isset($_POST['delete_service'])) {
    $service->delete($_POST['service_id'], $barber_id);
}

$services = $service->getByBarber($barber_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Coiffeur Elite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-darker text-white">
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar p-4" id="sidebar">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <h3 class="text-gold mb-0 sidebar-text">BARBER</h3>
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            </div>
            
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line me-2"></i> <span class="sidebar-text">Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="services.php" class="nav-link active">
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
                    <h2 class="mb-0">Manage Services</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-gold btn-sm d-none d-sm-block" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-2"></i> Add Service
                    </button>
                    <div class="text-gray-text d-none d-sm-block border-start ps-3">
                        <span class="text-gold">Barber Panel</span>
                    </div>
                </div>
            </header>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-gold mb-0"><i class="fas fa-concierge-bell me-2"></i> Your Services</h4>
                <button class="btn btn-gold btn-sm d-sm-none" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="row g-4">
                <?php foreach($services as $s): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card-luxury p-0 overflow-hidden h-100">
                        <img src="<?php echo $s['image'] ? '/uploads/services/'.$s['image'] : 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=400&q=80'; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="text-white mb-0"><?php echo $s['name']; ?></h5>
                                <span class="text-gold fw-bold"><?php echo $s['price']; ?> MAD</span>
                            </div>
                            <p class="text-gray-text small"><i class="far fa-clock me-2"></i> <?php echo $s['duration']; ?> Minutes</p>
                            <form action="services.php" method="POST" onsubmit="return confirm('Delete this service?');">
                                <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                                <button type="submit" name="delete_service" class="btn btn-sm btn-outline-danger w-100 mt-3">Delete Service</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($services)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="card-luxury p-5">
                            <i class="fas fa-concierge-bell fa-3x text-secondary mb-3"></i>
                            <p class="text-gray-text">You haven't added any services yet.</p>
                            <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addServiceModal">Add Your First Service</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-darker border-gold card-luxury">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-gold">Add New Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="services.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-gray-text">Service Name</label>
                            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. Haircut & Styling">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-text">Duration (Minutes)</label>
                            <input type="number" name="duration" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. 30">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-text">Price (MAD)</label>
                            <input type="number" name="price" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. 150">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-text">Service Image</label>
                            <input type="file" name="image" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_service" class="btn btn-gold px-4">Save Service</button>
                    </div>
                </form>
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
