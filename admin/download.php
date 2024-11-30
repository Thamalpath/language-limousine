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

// Handle Excel Download
if(isset($_POST['download'])) {
    $selectedDate = $_POST['date'];
    
    // Check if date is empty
    if(empty($selectedDate)) {
        $_SESSION['error'] = "Please select a date to download data.";
        header("Location: download");
        exit();
    }

    // Query to fetch data
    $query = "SELECT ID, Date, Trip, actual_arrival_time, arr_time_dep_pu_time, Flight, DI, M_or_F, 
            student_number, student_given_name, student_family_name, host_given_name, host_family_name, 
            Phone, Address, City, Special_instructions, Study_Permit, School, staff_member_assigned, 
            client, driverId, waiting_for_student_at_airport, student_in_car_to_host, 
            student_delivered_to_homestay_home, feedback 
            FROM students 
            WHERE Date = :date";
            
    $stmt = $pdo->prepare($query);
    $stmt->execute(['date' => $selectedDate]);
    
    // Check if any records exist for selected date
    if($stmt->rowCount() == 0) {
        $_SESSION['error'] = "No data found for selected date.";
        header("Location: download");
        exit();
    }
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="students_data_'.$selectedDate.'.xls"');
    
    // Create Excel content
    echo '<table border="1">';
    
    // Headers
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Date</th>';
    echo '<th>Trip</th>';
    echo '<th>Actual Arrival Time</th>';
    echo '<th>Arr Time Dep PU Time</th>';
    echo '<th>Flight</th>';
    echo '<th>DI</th>';
    echo '<th>Gender</th>';
    echo '<th>Student Number</th>';
    echo '<th>Student Given Name</th>';
    echo '<th>Student Family Name</th>';
    echo '<th>Host Given Name</th>';
    echo '<th>Host Family Name</th>';
    echo '<th>Phone</th>';
    echo '<th>Address</th>';
    echo '<th>City</th>';
    echo '<th>Special Instructions</th>';
    echo '<th>Study Permit</th>';
    echo '<th>School</th>';
    echo '<th>Staff Member Assigned</th>';
    echo '<th>Client</th>';
    echo '<th>Driver ID</th>';
    echo '<th>Waiting at Airport</th>';
    echo '<th>Student in Car</th>';
    echo '<th>Delivered to Homestay</th>';
    echo '<th>Feedback</th>';
    echo '</tr>';
    
    // Data rows
    while($row = $stmt->fetch()) {
        echo '<tr>';
        foreach($row as $value) {
            echo '<td>'.htmlspecialchars($value).'</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    exit();
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
											<div class="card border-top border-0 border-4 border-primary">
												<div class="card-body p-5">
                                                    <form method="POST">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="col-md-4">
                                                                <label for="date" class="form-label">Select Date</label>
                                                                <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>">
                                                            </div>
                                                            <div class="col-md-4 mt-4">
                                                                <button type="submit" name="download" class="btn btn-gradient-info fw-bold px-5">Download</button>
                                                            </div>
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
			</div>
		</div>
	    <!--end page wrapper -->

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>