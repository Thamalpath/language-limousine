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
function getTableCounts($pdo) {
    $tables = ['students', 'drivers',  'greeters', 'school'];
    $counts = [];

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
        $stmt->execute();
        $counts[$table] = $stmt->fetchColumn();
    }

    return $counts;
}

$tableCounts = getTableCounts($pdo);
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
				<div class="row row-cols-1 row-cols-lg-4">
					<!-- Students Count Card -->
					<div class="col">
						<div class="card rounded-4 bg-gradient-rainbow bubble position-relative overflow-hidden">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between mb-0">
									<div>
										<h4 class="mb-0 text-white"><?php echo $tableCounts['students']; ?></h4>
										<p class="mb-0 text-white">Total Students</p>
									</div>
									<div class="fs-1 text-white">
										<i class='bx bx-user'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Drivers Count Card -->
					<div class="col">
						<div class="card rounded-4 bg-gradient-burning bubble position-relative overflow-hidden">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between mb-0">
									<div>
										<h3 class="mb-0 text-white"><?php echo $tableCounts['drivers']; ?></h3>
										<h6 class="mb-0 text-white">Total Drivers</h6>
									</div>
									<div class="fs-1 text-white">
										<i class='bx bx-car'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Subdrivers Count Card -->
					<div class="col">
						<div class="card rounded-4 bg-gradient-moonlit bubble position-relative overflow-hidden">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between mb-0">
									<div>
										<!-- <h4 class="mb-0 text-white"><?php echo $tableCounts['subdrivers']; ?></h4> -->
										<p class="mb-0 text-white">Total Subdrivers</p>
									</div>
									<div class="fs-1 text-white">
										<i class='bx bx-user-circle'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Greeters Count Card -->
					<div class="col">
						<div class="card rounded-4 bg-gradient-cosmic bubble position-relative overflow-hidden">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between mb-0">
									<div>
										<h4 class="mb-0 text-white"><?php echo $tableCounts['greeters']; ?></h4>
										<p class="mb-0 text-white">Total Greeters</p>
									</div>
									<div class="fs-1 text-white">
										<i class='bx bx-hand'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!-- end row -->
			</div><!-- end page-content -->
		</div><!-- end page-wrapper -->

			</div>
		</div>
		<!--end page wrapper -->

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>