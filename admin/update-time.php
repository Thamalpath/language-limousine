<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

if (isset($_POST['update_time'])) {
    $student_id = $_POST['student_id'];
    $current_time = date('H:i:s');

    // Update query to set the current time
    $update_query = "UPDATE students SET waiting_for_student_at_airport = :waiting_time WHERE ID = :student_id";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(':waiting_time', $current_time, PDO::PARAM_STR);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Time updated for student ID: $student_id.";
    } else {
        $_SESSION['error_message'] = "Failed to update time for student ID: $student_id.";
    }

    // Redirect to the same page to avoid form resubmission
    header("Location: update-time");
    exit();
}

function displayStudents($pdo) {
    $current_date = date('Y-m-d');
    $query = "SELECT * FROM students WHERE Date = :Date AND student_in_car_to_host IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':Date', $current_date, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($results)) {
        $rows = '';
        foreach ($results as $row) {
            $time_button = !empty($row['waiting_for_student_at_airport']) ? 
                        $row['waiting_for_student_at_airport'] : 'Time';

            $rows .= "<tr style='text-align:center'>
                        <td>
                            <form method='POST'>
                                <input type='hidden' name='student_id' value='{$row['ID']}'>
                                <input type='submit' name='update_time' value='$time_button' class='btn btn-primary'>
                            </form>
                        </td>
                        <td>{$row['student_number']}</td>
                        <td>{$row['student_given_name']}</td>
                        <td>{$row['ID']}</td>
                        <td>{$row['arr_time_dep_pu_time']}</td>
                        <td>{$row['Flight']}</td>
                    </tr>";
        }
        return $rows;
    } else {
        return "<tr><td colspan='12'>No records found for today.</td></tr>";
    }
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
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Waiting</th>
                                                <th>Student Number</th>
                                                <th>Student Given Name</th>
                                                <th>ID</th>
                                                <th>Arrival Time</th>
                                                <th>Flight</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php echo displayStudents($pdo); ?>
                                        </tbody>
                                    </table>
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