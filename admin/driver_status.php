<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

function fetchAllDrivers($pdo) {
    $query = "SELECT driver_id, username, status FROM drivers";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$drivers = fetchAllDrivers($pdo);

// Handle form submission to update driver status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    foreach ($_POST['status'] as $driver_id => $status) {
        updateDriverStatus($pdo, $driver_id, $status);
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh to see updated statuses
    exit();
}

function updateDriverStatus($pdo, $driver_id, $status) {
    $query = "UPDATE drivers SET status = :status WHERE driver_id = :driver_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
    return $stmt->execute();
}
?>

<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />

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
                                            <main class="container">
                                                <h1>Update Driver Status</h1>
                                                <form method="POST">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Driver ID</th>
                                                                <th>Username</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($drivers as $driver): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($driver['driver_id']); ?></td>
                                                                    <td><?php echo htmlspecialchars($driver['username']); ?></td>
                                                                    <td>
                                                                        <select name="status[<?php echo $driver['driver_id']; ?>]">
                                                                            <option value="onduty" <?php echo $driver['status'] === 'onduty' ? 'selected' : ''; ?>>On Duty</option>
                                                                            <option value="offduty" <?php echo $driver['status'] === 'offduty' ? 'selected' : ''; ?>>Off Duty</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                </form>
                                            </main>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
		</div>
		<!--end page wrapper -->

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>


				