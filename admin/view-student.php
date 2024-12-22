<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');

include 'config/dbcon.php';

// Check if the user is authorized
if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'Admin') {
    header("Location: ./");
    exit();
}

$students = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_date'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE Date = :selected_date");
        $stmt->execute(['selected_date' => $_POST['selected_date']]);
        $students = $stmt->fetchAll();
        
        if (empty($students)) {
            $_SESSION['error'] = "No students found for selected date.";
            header("Location: view-student");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error fetching students: " . $e->getMessage();
        header("Location: view-student");
        exit();
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

<!-- Start main wrapper -->
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
                                            <form method="POST" class="mb-4">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        <label for="selected_date" class="form-label fw-bold fs-6">Select Date</label>
                                                        <input type="date" class="form-control" name="selected_date" required>
                                                    </div>
                                                    <div class="col-md-2 mt-4">
                                                        <button type="submit" class="btn btn-gradient-info fw-bold px-5">View Students</button>
                                                    </div>
                                                </div>
                                            </form>

                                            <!-- Students Table -->
                                            <?php if (!empty($students)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                        <th>Trip</th>
                                                        <th>Actual arrival time</th>
                                                        <th>Arrival Time</th>
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
                                                        <th>School</th>
                                                        <th>Client</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($student['Trip']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['actual_arrival_time']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['arr_time_dep_pu_time']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['Flight']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['DI']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['M_or_F']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['student_given_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['student_family_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['host_given_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['host_family_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['Phone']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['Address']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['City']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['School']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['client']); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php endif; ?>
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

<?php include 'partials/footer.php'; ?>
