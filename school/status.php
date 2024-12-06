<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');
include 'config/dbcon.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'School' || !isset($_SESSION['school_id'])) {
    header("Location: ./");
    exit();
}

$schoolId = $_SESSION['school_id'];

// Add this code after the $schoolId declaration and before the HTML table

// Prepare and execute query to get students for specific school
$stmt = $pdo->prepare("SELECT *
    FROM `students` 
    WHERE `client` = ?
    ORDER BY `ID` DESC");

$stmt->execute([$schoolId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />

    <!--start navbar-->
    <header class="top-header">
        <?php include 'partials/navbar.php';?>
    </header>
    <!--end top navbar-->

    <!--start sidebar-->
        <?php include 'partials/sidebar.php';?>
    <!--end sidebar-->

    <!--start main wrapper-->
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
                                            <h2 class="mt-3 mb-5 fw-bold">Student Status Data</h2>
                                        </div>

                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Actual arrival time</th>
                                                            <th>Arr time</th>
                                                            <th>student number</th>
                                                            <th>student given name</th>
                                                            <th>student family name</th>
                                                            <th>host given name</th>
                                                            <th>Waiting</th>
                                                            <th>In car</th>
                                                            <th>Delivered</th>
                                                            
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($students)): ?>
                                                            <?php foreach ($students as $index => $user): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($index + 1); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['actual_arrival_time']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['arr_time_dep_pu_time']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['student_number']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['student_given_name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['student_family_name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['host_given_name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['waiting_for_student_at_airport']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['student_in_car_to_host']); ?></td>
                                                                    <td><?php echo htmlspecialchars($user['student_delivered_to_homestay_home']); ?></td>
                                                                    
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
