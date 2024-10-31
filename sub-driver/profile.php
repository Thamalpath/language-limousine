<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Toronto');

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'SubDriver') {
    header("Location: ./");
    exit();
}

// Fetch sub-driver data using the session user_id
$stmt = $pdo->prepare("SELECT * FROM `sub-drivers` WHERE subdri_id = :subdri_id");
$stmt->execute(['subdri_id' => $_SESSION['user_id']]);
$subdriver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $driverId = $_POST['driverId'];
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

    // Prepare SQL query based on whether password is being updated
    if (!empty($password)) {
        $stmt = $pdo->prepare('UPDATE `sub-drivers` SET username = ?, email = ?, password = ?, driverId = ?, vehicle_no = ?, status = ? WHERE subdri_id = ?');
        $params = [$username, $email, $hashedPassword, $driverId, $vehicle_no, $status, $userId];
    } else {
        $stmt = $pdo->prepare('UPDATE `sub-drivers` SET username = ?, email = ?, driverId = ?, vehicle_no = ?, status = ? WHERE subdri_id = ?');
        $params = [$username, $email, $driverId, $vehicle_no, $status, $userId];
    }

    if ($stmt->execute($params)) {
        $_SESSION['success'] = 'Success: Profile updated successfully';
        header('Location: profile');
        exit;
    } else {
        $_SESSION['error'] = 'Error: Failed to update profile';
        header('Location: profile');
        exit;
    }
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
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($subdriver['username']) ?>" required>
                            </div>
                            <div class="col-md-7">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($subdriver['email']) ?>" required>
                            </div>

                            <div class="col-md-5">
                                <label for="driverId" class="form-label fw-bold">Driver ID</label>
                                <input type="text" class="form-control" name="driverId" value="<?= htmlspecialchars($subdriver['driverId']) ?>" required>
                            </div>
                            <div class="col-md-7">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Enter new password to change">
                            </div>

                            <div class="col-md-6">
                                <label for="vehicle_no" class="form-label fw-bold">Vehicle No</label>
                                <input type="text" class="form-control" name="vehicle_no" value="<?= htmlspecialchars($subdriver['vehicle_no']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="onduty" <?= $subdriver['status'] == 'onduty' ? 'selected' : '' ?>>On Duty</option>
                                    <option value="offduty" <?= $subdriver['status'] == 'offduty' ? 'selected' : '' ?>>Off Duty</option>
                                </select>
                            </div>

                            <div class="col-md-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary px-5">Update Profile</button>
                                <button type="reset" class="btn btn-secondary px-5">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--end main wrapper-->

<?php include 'partials/footer.php';?>
