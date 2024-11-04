<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start(); 
session_start();
$message = ob_get_clean();
include 'config/dbcon.php';
date_default_timezone_set('America/Vancouver');

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

//Retrieve User Data
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT username, email, role FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Error: User not found';
    header('Location: ./');
    exit;
}

//Update User Data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userId = $_SESSION['user_id'];

    // Validate and encrypt password if provided
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Error: Password must be at least 8 characters long';
            header('Location: user-profile');
            exit;
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    }

    // Prepare SQL query
    if (!empty($password)) {
        $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?');
        $params = [$username, $email, $hashedPassword, $userId];
    } else {
        $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ? WHERE user_id = ?');
        $params = [$username, $email, $userId];
    }

    // Execute the query
    if ($stmt->execute($params)) {
        $_SESSION['success'] = 'Success: User updated successfully';
        header('Location: dashboard');
        exit;
    } else {
        $_SESSION['error'] = 'Error: Failed to update user';
    }
    header('Location: user-profile');
    exit;
}

?>

<?php include 'partials/header.php';?>

	<!--wrapper-->
	<div class="wrapper">

		<!--sidebar wrapper -->
		<?php include 'partials/sidebar.php';?>
		<!--end sidebar wrapper -->

		<!--start header -->
		<header>
			<div class="topbar d-flex align-items-center">
				<?php include 'partials/navbar.php';?>
			</div>
		</header>
		<!--end header -->

        <!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">

                <div class="row row-cols-12 row-cols-md-12 row-cols-lg-12 row-cols-xl-12">
					<div class="col mt-4">
						<div class="card">
							<div class="card-body">
                                <div class="tab-content py-3">
                                    <div class="row">
                                        <div class="col-xl-12 mx-auto">
                                            <div class="card border-top border-0 border-4 border-primary">
                                                <div class="card-body p-5">
                                                    <div class="card-title d-flex align-items-center">
                                                        <h5 class="mb-0 text-primary font-24 fw-bold">User Profile</h5>
                                                    </div>
                                                    <hr>
                                                    <form class="row g-3 needs-validation" method="POST" novalidate>
                                                        <div class="col-md-6">
                                                            <label for="username" class="form-label fw-bold font-18">Userame</label>
                                                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
											                <div class="invalid-feedback">Please provide a username.</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="email" class="form-label fw-bold font-18">Email</label>
                                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
											                <div class="invalid-feedback">Please provide an email.</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="password" class="form-label fw-bold font-18">Password</label>
                                                            <input type="password" class="form-control" name="password">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="role" class="form-label fw-bold font-18">Role</label>
                                                            <input type="text" class="form-control" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                                                        </div>
                                                        <div class="col-md-12 text-center g-3 mt-5">
                                                            <button type="submit" class="btn btn-primary px-5 w-100">Submit</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
		<!--end page wrapper -->

        <?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>