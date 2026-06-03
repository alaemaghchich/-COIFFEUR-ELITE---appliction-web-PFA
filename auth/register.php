<?php
include_once '../config/db.php';
include_once '../classes/Barber.php';
include_once '../classes/Customer.php';

$database = new Database();
$db = $database->getConnection();

$error = "";
$success = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    
    if($role == 'customer') {
        $customer = new Customer($db);
        $customer->full_name = $_POST['full_name'];
        $customer->email = $_POST['email'] ?: null;
        $customer->phone = $_POST['phone'];
        $customer->password = $_POST['password'];
        $customer->gender = $_POST['gender'];
        $customer->city = $_POST['city'];
        
        // Profile Pic Upload
        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $target_dir = "../uploads/profiles/";
            $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_dir . $file_name);
            $customer->profile_pic = $file_name;
        }

        $res = $customer->registerCustomer();
        if($res === true) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = $res ?: "Registration failed.";
        }
    } elseif($role == 'barber') {
        $barber = new Barber($db);
        $barber->full_name = $_POST['full_name'];
        $barber->email = $_POST['email'] ?: null;
        $barber->phone = $_POST['phone'];
        $barber->password = $_POST['password'];
        $barber->gender = $_POST['gender'];
        $barber->city = $_POST['city'];
        $barber->age = $_POST['age'];
        $barber->bio = $_POST['bio'];
        $barber->experience_years = $_POST['experience_years'];
        $barber->salon_name = $_POST['salon_name'];
        $barber->salon_type = $_POST['salon_type'];
        $barber->work_start = $_POST['work_start'];
        $barber->work_end = $_POST['work_end'];
        $barber->lat = $_POST['lat'];
        $barber->lon = $_POST['lon'];

        // File Uploads
        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $barber->profile_pic = time() . "_p_" . basename($_FILES["profile_pic"]["name"]);
            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "../uploads/profiles/" . $barber->profile_pic);
        }
        if(isset($_FILES['salon_logo']) && $_FILES['salon_logo']['error'] == 0) {
            $barber->salon_logo = time() . "_l_" . basename($_FILES["salon_logo"]["name"]);
            move_uploaded_file($_FILES["salon_logo"]["tmp_name"], "../uploads/salons/" . $barber->salon_logo);
        }
        if(isset($_FILES['salon_img']) && $_FILES['salon_img']['error'] == 0) {
            $barber->salon_img = time() . "_s_" . basename($_FILES["salon_img"]["name"]);
            move_uploaded_file($_FILES["salon_img"]["tmp_name"], "../uploads/salons/" . $barber->salon_img);
        }
        if(isset($_FILES['diploma']) && $_FILES['diploma']['error'] == 0) {
            $barber->diploma_or_video = time() . "_d_" . basename($_FILES["diploma"]["name"]);
            move_uploaded_file($_FILES["diploma"]["tmp_name"], "../uploads/diplomas/" . $barber->diploma_or_video);
        }

        $res = $barber->registerBarber();
        if($res === true) {
            $success = "Registration submitted! Waiting for admin approval.";
        } else {
            $error = $res ?: "Registration failed.";
        }
    }
}

$cities = ["Casablanca", "Rabat", "Marrakech", "Fes", "Tangier", "Agadir", "Meknes", "Oujda", "Kenitra", "Tetouan"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Coiffeur Elite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .form-section { display: none; }
        .form-section.active { display: block; }
    </style>
</head>
<body class="bg-darker py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-luxury p-5 shadow-lg">
                    <div class="text-center mb-5">
                        <a href="/index.php" class="btn btn-outline-gold btn-sm mb-4">
                            <i class="fas fa-home me-2"></i> Return to Home
                        </a>
                        <h2 class="text-gold fw-bold">JOIN COIFFEUR ELITE</h2>
                        <p class="text-gray-text">Become part of the elite grooming community.</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-3 mb-5">
                        <button type="button" class="btn btn-outline-gold px-4" id="btnCustomer">I'm a Customer</button>
                        <button type="button" class="btn btn-outline-gold px-4" id="btnBarber">I'm a Barber</button>
                    </div>

                    <form action="register.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="role" id="roleInput" value="customer">
                        
                        <!-- Common Fields -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-gray-text">Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-text">Email (Optional)</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-text">Phone Number</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-text">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-text">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-text">City</label>
                                <select name="city" class="form-select" required>
                                    <?php foreach($cities as $city): ?>
                                        <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-gray-text">Profile Picture</label>
                                <input type="file" name="profile_pic" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <!-- Barber Specific Fields -->
                        <div id="barberFields" class="form-section">
                            <h4 class="text-gold mb-4 border-bottom border-secondary pb-2">Salon Details</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label text-gray-text">Age</label>
                                    <input type="number" name="age" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-gray-text">Experience (Years)</label>
                                    <input type="number" name="experience_years" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-gray-text">Salon Type</label>
                                    <select name="salon_type" class="form-select">
                                        <option value="men">Men Only</option>
                                        <option value="women">Women Only</option>
                                        <option value="unisex">Unisex</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-gray-text">Salon Name</label>
                                    <input type="text" name="salon_name" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-gray-text">Bio</label>
                                    <textarea name="bio" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Work Start</label>
                                    <input type="time" name="work_start" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Work End</label>
                                    <input type="time" name="work_end" class="form-control">
                                </div>
                                
                                <!-- GPS / Location -->
                                <div class="col-md-12">
                                    <label class="form-label text-gray-text">Location (GPS)</label>
                                    <div class="input-group">
                                        <input type="text" name="lat" id="lat" class="form-control" placeholder="Latitude" readonly>
                                        <input type="text" name="lon" id="lon" class="form-control" placeholder="Longitude" readonly>
                                        <button type="button" class="btn btn-gold" onclick="getLocation()">Get GPS</button>
                                    </div>
                                    <small class="text-muted">Click the button to set your salon's exact location.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Salon Logo (Optional)</label>
                                    <input type="file" name="salon_logo" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-gray-text">Salon Image</label>
                                    <input type="file" name="salon_img" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-gray-text">Diploma / Video of work</label>
                                    <input type="file" name="diploma" class="form-control" accept="image/*,video/*">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 py-3 mt-4">Create Account</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-gray-text small">Already have an account? <a href="login.php" class="text-gold">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const btnCustomer = document.getElementById('btnCustomer');
        const btnBarber = document.getElementById('btnBarber');
        const barberFields = document.getElementById('barberFields');
        const roleInput = document.getElementById('roleInput');

        btnCustomer.addEventListener('click', () => {
            barberFields.classList.remove('active');
            roleInput.value = 'customer';
            btnCustomer.classList.add('btn-gold');
            btnCustomer.classList.remove('btn-outline-gold');
            btnBarber.classList.remove('btn-gold');
            btnBarber.classList.add('btn-outline-gold');
        });

        btnBarber.addEventListener('click', () => {
            barberFields.classList.add('active');
            roleInput.value = 'barber';
            btnBarber.classList.add('btn-gold');
            btnBarber.classList.remove('btn-outline-gold');
            btnCustomer.classList.remove('btn-gold');
            btnCustomer.classList.add('btn-outline-gold');
        });

        // Initialize state
        btnCustomer.click();

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    document.getElementById('lat').value = position.coords.latitude;
                    document.getElementById('lon').value = position.coords.longitude;
                }, (error) => {
                    alert("Error getting location: " + error.message);
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>
</body>
</html>
