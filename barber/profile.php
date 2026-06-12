<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'barber') {
    header("Location: /auth/login.php");
    exit();
}

include_once '../config/db.php';
include_once '../classes/Barber.php';

$database = new Database();
$db = $database->getConnection();
$barber = new Barber($db);

$barber_id = $_SESSION['user_id'];
$message = "";
$status = "";

if(isset($_POST['update_profile'])) {
    $files = [];
    
    // Handle Profile Picture
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $img_name = "p_" . time() . "_" . basename($_FILES["profile_pic"]["name"]);
        if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "../uploads/profiles/" . $img_name)) {
            $files['profile_pic'] = $img_name;
        }
    }

    // Handle Salon Logo
    if(isset($_FILES['salon_logo']) && $_FILES['salon_logo']['error'] == 0) {
        $img_name = "l_" . time() . "_" . basename($_FILES["salon_logo"]["name"]);
        if(move_uploaded_file($_FILES["salon_logo"]["tmp_name"], "../uploads/salons/" . $img_name)) {
            $files['salon_logo'] = $img_name;
        }
    }

    // Handle Salon Image
    if(isset($_FILES['salon_img']) && $_FILES['salon_img']['error'] == 0) {
        $img_name = "s_" . time() . "_" . basename($_FILES["salon_img"]["name"]);
        if(move_uploaded_file($_FILES["salon_img"]["tmp_name"], "../uploads/salons/" . $img_name)) {
            $files['salon_img'] = $img_name;
        }
    }

    $data = $_POST;
    $data['user_id'] = $barber_id;
    
    $result = $barber->updateProfile($data, $files);
    if($result === true) {
        $message = "Profile updated successfully!";
        $status = "success";
    } else {
        $message = "Error: " . $result;
        $status = "danger";
    }
}

$details = $barber->getBarberDetails($barber_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Coiffeur Elite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .gps-btn {
            white-space: nowrap;
        }
    </style>
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
                    <a href="services.php" class="nav-link">
                        <i class="fas fa-concierge-bell me-2"></i> <span class="sidebar-text">Services</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link active">
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
                    <h2 class="mb-0">Edit Profile</h2>
                </div>
                <div class="text-gray-text d-none d-sm-block">
                    <img src="<?php echo $details['profile_pic'] ? '/uploads/profiles/'.$details['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($details['full_name']); ?>" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;">
                    <span class="text-gold"><?php echo $details['full_name']; ?></span>
                </div>
            </header>

            <?php if($message): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show bg-dark text-<?php echo $status; ?> border-<?php echo $status; ?>" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Personal Information -->
                    <div class="col-xl-6">
                        <div class="card-luxury p-4 h-100">
                            <h4 class="text-gold mb-4"><i class="fas fa-user me-2"></i> Personal Details</h4>
                            
                            <div class="text-center mb-4">
                                <img src="<?php echo $details['profile_pic'] ? '/uploads/profiles/'.$details['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($details['full_name']); ?>" class="rounded-circle mb-3 border border-gold" width="120" height="120" style="object-fit: cover; border-width: 3px !important;">
                                <div class="mb-3">
                                    <label class="form-label text-gray-text">Update Profile Picture</label>
                                    <input type="file" name="profile_pic" class="form-control bg-dark text-white border-secondary mb-2">
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-gray-text">Full Name</label>
                                    <input type="text" name="full_name" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['full_name']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Email Address</label>
                                    <input type="email" name="email" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['email']; ?>" >
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Phone Number</label>
                                    <input type="text" name="phone" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['phone']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">City</label>
                                    <input type="text" name="city" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['city']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Gender</label>
                                    <select name="gender" class="form-select bg-dark text-white border-secondary">
                                        <option value="male" <?php echo $details['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo $details['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Experience (Years)</label>
                                    <input type="number" name="experience_years" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['experience_years']; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-gray-text">New Password (leave blank to keep current)</label>
                                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salon Information -->
                    <div class="col-xl-6">
                        <div class="card-luxury p-4 h-100">
                            <h4 class="text-gold mb-4"><i class="fas fa-store me-2"></i> Salon Details</h4>
                            
                            <div class="row g-3">
                                <div class="col-12 text-end mb-2">
                                    <button type="button" onclick="getLocation()" class="btn btn-sm btn-outline-info gps-btn">
                                        <i class="fas fa-location-crosshairs me-2"></i> Get Current GPS
                                    </button>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-gray-text">Salon Name</label>
                                    <input type="text" name="salon_name" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['salon_name']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Salon Type</label>
                                    <select name="salon_type" class="form-select bg-dark text-white border-secondary">
                                        <option value="men" <?php echo $details['salon_type'] == 'men' ? 'selected' : ''; ?>>Men Only</option>
                                        <option value="women" <?php echo $details['salon_type'] == 'women' ? 'selected' : ''; ?>>Women Only</option>
                                        <option value="unisex" <?php echo $details['salon_type'] == 'unisex' ? 'selected' : ''; ?>>Unisex</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Work Hours</label>
                                    <div class="input-group">
                                        <input type="time" name="work_start" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['work_start']; ?>">
                                        <span class="input-group-text bg-dark border-secondary text-gray-text">to</span>
                                        <input type="time" name="work_end" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['work_end']; ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-gray-text">Bio / Description</label>
                                    <textarea name="bio" class="form-control bg-dark text-white border-secondary" rows="4"><?php echo $details['bio']; ?></textarea>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Latitude</label>
                                    <input type="text" name="lat" id="lat" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['lat']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Longitude</label>
                                    <input type="text" name="lon" id="lon" class="form-control bg-dark text-white border-secondary" value="<?php echo $details['lon']; ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Salon Logo</label>
                                    <input type="file" name="salon_logo" class="form-control bg-dark text-white border-secondary mb-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Salon Image</label>
                                    <input type="file" name="salon_img" class="form-control bg-dark text-white border-secondary mb-2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" name="update_profile" class="btn btn-gold px-5 py-2"><i class="fas fa-save me-2"></i> Save Changes</button>
                    </div>
                </div>
            </form>
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

        function getLocation() {
            if (navigator.geolocation) {
                const btn = document.querySelector('.gps-btn');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Fetching...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        document.getElementById("lat").value = position.coords.latitude.toFixed(8);
                        document.getElementById("lon").value = position.coords.longitude.toFixed(8);
                        btn.innerHTML = '<i class="fas fa-check me-2"></i> Location Saved';
                        btn.classList.replace('btn-outline-info', 'btn-success');
                        setTimeout(() => {
                            btn.innerHTML = '<i class="fas fa-location-crosshairs me-2"></i> Get Current GPS';
                            btn.classList.replace('btn-success', 'btn-outline-info');
                            btn.disabled = false;
                        }, 3000);
                    },
                    (error) => {
                        alert("Error getting location: " + error.message);
                        btn.innerHTML = '<i class="fas fa-location-crosshairs me-2"></i> Get Current GPS';
                        btn.disabled = false;
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
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
