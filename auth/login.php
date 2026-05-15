<?php
session_start();

include_once '../config/db.php';
include_once '../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    $login_status = $user->login($identifier, $password);

    if($login_status === true) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->full_name;
        $_SESSION['user_role'] = $user->role;

        if($user->role == 'admin') {
            header("Location: /admin/dashboard.php");
        } elseif($user->role == 'barber') {
            header("Location: /barber/dashboard.php");
        } else {
            header("Location: /index.php");
        }
        exit();
    } else {
        $error = $login_status ?: "Invalid username, email, or phone.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BarberHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-darker vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card-luxury p-5 shadow-lg">
                    <div class="text-center mb-5">
                        <h2 class="text-gold fw-bold">BARBERHUB</h2>
                        <p class="text-gray-text">Welcome back, Gentleman.</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label text-gray-text">Username, Email, or Phone</label>
                            <input type="text" name="identifier" class="form-control" required placeholder="Enter your credentials">
                        </div>
                        <div class="mb-5">
                            <label class="form-label text-gray-text">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-gold w-100 py-3 mb-4">Login</button>
                        <div class="text-center">
                            <p class="text-gray-text small">Don't have an account? <a href="register.php" class="text-gold">Sign up</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
