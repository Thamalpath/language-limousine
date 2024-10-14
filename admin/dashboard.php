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
					<div class="col">
						<div class="card rounded-4 bg-gradient-rainbow bubble position-relative overflow-hidden">
							<div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                   <div class="">
									  <h4 class="mb-0 text-white">986</h4>
									  <p class="mb-0 text-white">Total Orders</p>
								   </div>
								   <div class="fs-1 text-white">
									<i class='bx bx-cart'></i>
								   </div>
								</div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card rounded-4 bg-gradient-burning bubble position-relative overflow-hidden">
							<div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-0">
									<div class="">
										<h3 class="mb-0 text-white">986</h3>
										<h6 class="mb-0 text-white">Patients</h6>
									</div>
								   <div class="fs-1 text-white">
									<i class='bx bx-user-plus'></i>
								   </div>
								</div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card rounded-4 bg-gradient-moonlit bubble position-relative overflow-hidden">
							<div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                   <div class="">
									  <h4 class="mb-0 text-white">$24K</h4>
									  <p class="mb-0 text-white">Total Revenue</p>
								   </div>
								   <div class="fs-1 text-white">
									  <i class='bx bx-wallet' ></i>
								   </div>
								</div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card rounded-4 bg-gradient-cosmic bubble position-relative overflow-hidden">
							<div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                   <div class="">
									  <h4 class="mb-0 text-white">22%</h4>
									  <p class="mb-0 text-white">Total Growth</p>
								   </div>
								   <div class="fs-1 text-white">
									 <i class='bx bx-line-chart-down'></i>
								   </div>
								</div>
							</div>
						</div>
					</div>
				</div><!--end row--> 
			</div>
		</div>
		<!--end page wrapper -->

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>