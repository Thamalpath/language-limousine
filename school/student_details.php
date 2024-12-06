<?php
// Error reporting setup for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
date_default_timezone_set('America/Vancouver');
include 'config/dbcon.php';

// Check if the user is authorized
if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'School') {
    header("Location: ./");
    exit();
}

// Handle delete action
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE ID = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = "Success: Data deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: student_details");
    exit();
}

// Get the selected date filter, if set
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : '';

// Display students list
$school_id = $_SESSION['school_id'];
$query = "SELECT * FROM students WHERE client = :school_id";
if ($selected_date) {
    $query .= " AND Date = :selected_date";
}
$stmt = $pdo->prepare($query);
$stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
if ($selected_date) {
    $stmt->bindParam(':selected_date', $selected_date);
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'partials/header.php'; ?>
<link rel="stylesheet" href="assets/css/style.css" />

<header class="top-header">
    <?php include 'partials/navbar.php'; ?>
</header>

<!-- Start sidebar -->
<?php include 'partials/sidebar.php'; ?>
<!-- End sidebar -->

<!-- Start main wrapper -->
<main class="main-wrapper min-vh-100">
    <div class="main-content">

        <div class="row">
            <div class="col-xxl-12 d-flex align-items-stretch">
                <div class="card w-100 overflow-hidden rounded-4">
                    <div class="card-body position-relative p-4">
                        <div class="row">
                            <div class="col-12 col-xl-12">
                                <div class="card">
                                    <div class="card-body p-4">
                                        <!-- Date filter form -->
                                        <form method="GET" action="student_details" class="row g-3">
                                            <div class="col-md-3">
                                                <label for="selected_date" class="form-label fw-bold fs-6">Select the Date</label>
                                                <input type="date" name="selected_date" id="selected_date" class="form-control" value="<?php echo !empty($selected_date) ? htmlspecialchars($selected_date) : date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filter" class="form-label">&nbsp;</label>
                                                <button type="submit" class="btn btn-grd btn-grd-info px-5 fw-bold mt-4">Filter</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Action</th>
                                                        <th>#</th>
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
                                                        <th>Study permit</th>
                                                        <th>staff_member_assigned</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($students)): ?>
                                                        <?php foreach ($students as $index => $user): ?>
                                                            <tr data-id="<?php echo htmlspecialchars($user['ID']); ?>">
                                                                <td>
                                                                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                                        <a href="#" data-id="<?php echo $user['ID']; ?>" class="btn btn-grd btn-grd-danger px-4 delete-btn">Delete</a>
                                                                    </div>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($index + 1); ?></td>
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
                                                                <td><?php echo htmlspecialchars($user['Study_Permit']); ?></td>
                                                                <td><?php echo htmlspecialchars($user['staff_member_assigned']); ?></td>
                                                                
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
                        </div>
                    </div>
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

                // Custom confirmation dialog
                const confirmationDialog = document.createElement('div');
                confirmationDialog.classList.add('confirmation-dialog'); 
                confirmationDialog.innerHTML = `
                    <div>
                        <img src="assets/images/wired-outline-1140-error.gif" alt="Warning Icon" class="warning-icon">
                        <p class="fs-3 text-black fw-bold">Are you sure?</p>
                        <p class="fs-6" style="color: #6c757d;">You won't be able to revert this!</p>
                        <div class="button-container">
                            <button id="yesButton" class="btn btn-danger">Yes</button>
                            <button id="noButton" class="btn btn-secondary">No</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(confirmationDialog);

                // Yes and No button click handlers
                document.getElementById('yesButton').addEventListener('click', function() {
                    window.location.href = 'student_details?delete_id=' + deleteId;
                });

                document.getElementById('noButton').addEventListener('click', function() {
                    document.body.removeChild(confirmationDialog);
                    notyf.error('Delete canceled');
                });
            });
        });
    });
</script>
