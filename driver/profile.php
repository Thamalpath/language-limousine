<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Toronto');


include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'Driver') {
    header("Location: ./");
    exit();
}

// Fetch driver data using the session user_id
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE driver_id = :driver_id");
$stmt->execute(['driver_id' => $_SESSION['user_id']]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $driverId = $_POST['driverID'];
    $vehicle_no = $_POST['vehicle_no'];
    $status = $_POST['status'];
    $userId = $_SESSION['user_id'];

    // Validate and encrypt password if provided
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Error: Password must be at least 8 characters long';
            header('Location: profile');
            exit;
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    }

    // Prepare SQL query
    if (!empty($password)) {
        $stmt = $pdo->prepare('UPDATE drivers SET username = ?, email = ?, password = ?, gender = ?, driverId = ?, vehicle_no = ?, status = ? WHERE driver_id = ?');
        $params = [$username, $email, $hashedPassword, $gender, $driverId, $vehicle_no, $status, $userId];
    } else {
        $stmt = $pdo->prepare('UPDATE drivers SET username = ?, email = ?, gender = ?, driverId = ?, vehicle_no = ?, status = ? WHERE driver_id = ?');
        $params = [$username, $email, $gender, $driverId, $vehicle_no, $status, $userId];
    }

    // Execute the query
    if ($stmt->execute($params)) {
        $_SESSION['success'] = 'Success: Profile updated successfully';
        header('Location: profile');
        exit;
    } else {
        $_SESSION['error'] = 'Error: Failed to update profile';
    }
    
    header('Location: profile');
    exit;
}
?>

<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />

    <!--start navbar-->
    <header class="top-header">
        <?php include 'partials/navbar.php';?>
    </header>
    <!--end top navbar-->


    <!--start sidebar-->
        <?php include 'partials/sidebar.php';?>
    <!--end sidebar-->

    <!--start main wrapper-->
    <main class="main-wrapper min-vh-100">
        <div class="main-content">
            <div class="row">
                <div class="col-12 col-xl-12">
                    <div class="card rounded-4 border-top border-4 border-primary border-gradient-1">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="">
                                <h5 class="mb-0 fw-bold">Edit Profile</h5>
                            </div>
                        </div>
                        <form method="POST" class="row g-4">
                            <div class="col-md-5">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($driver['username']) ?>" placeholder="Username">
                            </div>
                            <div class="col-md-7 mb-4">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($driver['email']) ?>">
                            </div>

                            <div class="col-md-5">
                                <label for="gender" class="form-label fw-bold">Gender</label>
                                <input type="text" class="form-control" name="gender" value="<?= htmlspecialchars($driver['gender']) ?>">
                            </div>
                            <div class="col-md-7 mb-4">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Enter new password if you want to change">
                            </div>

                            <div class="col-md-4">
                                <label for="driverID" class="form-label fw-bold">Driver ID</label>
                                <input type="text" class="form-control" name="driverID" value="<?= htmlspecialchars($driver['driverId']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="vehicle_no" class="form-label fw-bold">Vehicle No</label>
                                <input type="text" class="form-control" name="vehicle_no" value="<?= htmlspecialchars($driver['vehicle_no']) ?>">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="status" class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="onduty" <?= $driver['status'] == 'onduty' ? 'selected' : '' ?>>On Duty</option>
                                    <option value="offduty" <?= $driver['status'] == 'offduty' ? 'selected' : '' ?>>Off Duty</option>
                                </select>
                            </div>

                            <div class="col-md-12 text-center g-3 mt-4 mb-5">
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    <button type="submit" class="btn btn-grd-info px-5 fw-bold text-white">Update Profile</button>
                                    <button type="reset" class="btn btn-grd-royal px-5 fw-bold text-white">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--end main wrapper-->

<?php include 'partials/footer.php';?>
