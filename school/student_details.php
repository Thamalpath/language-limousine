<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'School') {
    header("Location: ./");
    
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare("DELETE FROM students WHERE ID = ?");
        // Bind parameters and execute the statement
        $stmt->execute([$delete_id]);
        // Set success message
        $_SESSION['success'] = "Success: Data deleted successfully";
    } catch (PDOException $e) {
        // Set error message
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect back to the referring page
    header("Location: student_details");
    exit();
}
?>
<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />

<header class="top-header">
    <?php include 'partials/navbar.php'; ?>
</header>


<!-- start sidebar -->
<?php include 'partials/sidebar.php'; ?>
<!-- end sidebar -->

<!-- start main wrapper -->
<main class="main-wrapper min-vh-100">
    <div class="main-content">
        <div class="card">
            <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Trip</th>
                                <th>Actual arrival time</th>
                                <th>Arr time</th>
                                <th>Flight</th>
                                <th>D or I</th>
                                <th>M or F</th>
                                <th>student number</th>
                                <th>student given name</th>
                                <th>student family name</th>
                                <th>host given name</th>
                                <th>host family name</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>SI</th>
                                <th>Study permit</th>
                                <th>School</th>
                                <th>staff_member_assigned</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!isset($_SESSION['school_id'])) {
                                die('School ID not set in session.');
                            }
                            
                            
                            $school_id = $_SESSION['school_id'];

                            $query = "SELECT *  
                                        FROM students
                                        WHERE School = :school_id";
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
                            $stmt->execute();
                            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $index => $user): ?>
                                    <tr data-id="<?php echo isset($user['school_id']) ? htmlspecialchars($user['school_id']) : 'N/A'; ?>">
                                        <td><?php echo htmlspecialchars($index + 1); ?></td>
                                        <td><?php echo htmlspecialchars($user['Date']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Trip']); ?></td>
                                        <td><?php echo htmlspecialchars($user['actual_arrival_time']); ?></td>
                                        <td><?php echo htmlspecialchars($user['arr_time_dep_pu_time']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Flight']); ?></td>
                                        <td><?php echo htmlspecialchars($user['DI']); ?></td>
                                        <td><?php echo htmlspecialchars($user['M_or_F']); ?></td>
                                        <td><?php echo htmlspecialchars($user['student_number']); ?></td>
                                        <td><?php echo htmlspecialchars($user['student_given_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['student_family_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['host_given_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['host_family_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Phone']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Address']); ?></td>
                                        <td><?php echo htmlspecialchars($user['City']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Special_instructions']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Study_Permit']); ?></td>
                                        <td><?php echo htmlspecialchars($user['School']); ?></td>
                                        <td><?php echo htmlspecialchars($user['staff_member_assigned']); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                <a href="#" data-id="<?php echo $user['ID']; ?>" class="btn btn-grd btn-grd-danger px-4 delete-btn">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="21">No students found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>
</main>


<?php include 'partials/footer.php'; ?>
<script>
		document.addEventListener('DOMContentLoaded', function() {

			document.querySelectorAll('.delete-btn').forEach(function(button) {
				button.addEventListener('click', function(e) {
					e.preventDefault();
					const deleteId = this.getAttribute('data-id');
					
					// Create the custom confirmation dialog
					const confirmationDialog = document.createElement('div');
					confirmationDialog.classList.add('confirmation-dialog'); 
					confirmationDialog.innerHTML = `
						<div>
							<img src="assets/images/wired-outline-1140-error.gif" alt="Warning Icon" class="warning-icon">
							<p class="fs-3 text-black fw-bold">Are you sure ?</p>
							<p class="fs-6" style="color: #6c757d;">You won't be able to revert this!</p>
							<div class="button-container">
								<button id="yesButton" class="btn btn-danger">Yes</button>
								<button id="noButton" class="btn btn-secondary">No</button>
							</div>
						</div>
					`;
					document.body.appendChild(confirmationDialog);
					
					// Handle Yes button click
					document.getElementById('yesButton').addEventListener('click', function() {
						window.location.href = 'student_details?delete_id=' + deleteId;
					});
					
					// Handle No button click
					document.getElementById('noButton').addEventListener('click', function() {
						document.body.removeChild(confirmationDialog);
						notyf.error('Delete canceled');
					});
				});
			});
		});
	</script>