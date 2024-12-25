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

// Handle Update
if (isset($_POST['update_students'])) {
    $new_date = $_POST['new_date'];
    $student_ids = $_POST['student_ids'] ?? [];
    
    try {
        $update_stmt = $pdo->prepare("UPDATE students SET Date = ? WHERE ID = ?");
        $success_count = 0;
        
        foreach($student_ids as $student_id) {
            if ($update_stmt->execute([$new_date, $student_id])) {
                $success_count++;
            }
        }
        
        $_SESSION['success'] = "Updated date for $success_count students successfully!";
        header("Location: edit-student");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
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
								<div class="tab-content py-3">
									<div class="row">
										<div class="col-xl-12 mx-auto">
											<div class="card border-top border-0 border-4 border-primary">
                                            <div class="card-body p-5">
    <div class="card-title d-flex align-items-center">
        <h3 class="mb-0 text-primary fw-bold">Update Student Dates</h3>
    </div>
    <hr>
    
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Select Date</label>
            <input type="date" name="selected_date" class="form-control" required>
        </div>
        <div class="col-md-4">
            <button type="submit" name="filter_students" class="btn btn-primary mt-4">Filter Students</button>
        </div>
    </form>

    <?php
    if (isset($_POST['filter_students'])) {
        $selected_date = $_POST['selected_date'];
        $stmt = $pdo->prepare("SELECT * FROM students WHERE Date = ? AND student_delivered_to_homestay_home IS NULL");
        $stmt->execute([$selected_date]);
        $students = $stmt->fetchAll();
        
        if ($students) {
    ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Student Number</th>
                            <th>Student Name</th>
                            <th>Current Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><input type="checkbox" name="student_ids[]" value="<?= $student['ID'] ?>"></td>
                            <td><?= htmlspecialchars($student['student_number']) ?></td>
                            <td><?= htmlspecialchars($student['student_given_name'] . ' ' . $student['student_family_name']) ?></td>
                            <td><?= htmlspecialchars($student['Date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-4">
                    <label class="form-label">New Date</label>
                    <input type="date" name="new_date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="update_students" class="btn btn-gradient-info fw-bold px-5 mt-4">Update Selected Students</button>
                </div>
            </div>
        </form>

        <script>
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.getElementsByName('student_ids[]');
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            });
        </script>
    <?php
        } else {
            echo '<div class="alert alert-info">No students found for the selected date.</div>';
        }
    }
    ?>
</div>

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>
