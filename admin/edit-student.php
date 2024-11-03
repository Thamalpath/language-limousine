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

// Handle Update
if (isset($_POST['update_student'])) {
    $student_id = $_POST['student_id'];
    $new_date = $_POST['date'];
    
    try {
        $update_stmt = $pdo->prepare("UPDATE students SET Date = ? WHERE ID = ?");
        if ($update_stmt->execute([$new_date, $student_id])) {
            $_SESSION['success'] = "Student date updated successfully!";
            header("Location: edit-student");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: Unable to update student date. " . $e->getMessage();
    }
}?>

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
                                                    <div class="card-title d-flex align-items-center">
                                                        <h3 class="mb-0 text-primary fw-bold">Search Student</h3>
                                                    </div>
                                                    <hr>
                                                    <!-- Search Form -->
                                                    <form method="GET" class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Student Number</label>
                                                            <input type="text" name="student_number" class="form-control" placeholder="Enter Student Number" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button type="submit" class="btn btn-gradient-info fw-bold px-5 mt-4"><i class="bx bx-search"></i> Search</button>
                                                        </div>
                                                    </form>

                                                    <?php
                                                        if (isset($_GET['student_number'])) {
                                                            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_number = ?");
                                                            $stmt->execute([$_GET['student_number']]);
                                                            $student = $stmt->fetch();
                                                        
                                                        if ($student) {
                                                    ?>
                                                        <!-- Update Form -->
                                                        <form method="POST" class="row g-3 mt-4">
                                                            <input type="hidden" name="student_id" value="<?php echo $student['ID']; ?>">
                                                            
                                                            <div class="col-md-3">
                                                                <label class="form-label">Student Number</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_number']); ?>" readonly>
                                                            </div>                                                     
                                                            <div class="col-md-3">
                                                                <label class="form-label">Student Name</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_given_name'] . ' ' . $student['student_family_name']); ?>" readonly>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Current Date</label>
                                                                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($student['Date']); ?>" required>
                                                            </div>

                                                            <div class="col-md-3 mt-4 pt-3">
                                                                <button type="submit" name="update_student" class="btn btn-gradient-info fw-bold px-5">Update Date</button>
                                                            </div>
                                                        </form>
                                                    <?php
                                                        } else {
                                                            $_SESSION['error'] = "Error: No student found with this number.";                                       
                                                        }
                                                    }
                                                    ?>
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
