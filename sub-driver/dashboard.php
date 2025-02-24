<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'SubDriver') {
    header("Location: ./");
    exit();
}
if (isset($_POST['update_in_car'])) {
    $student_id = $_POST['student_id'];
    $current_time = date('H:i:s');

    // Update query to set the current time for "student_in_car_to_host"
    $update_query = "UPDATE students SET student_in_car_to_host = :in_car_time WHERE ID = :student_id";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(':in_car_time', $current_time, PDO::PARAM_STR);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>
            $('#example').DataTable().ajax.reload(null, false);
        </script>";
    } else {
        $_SESSION['error'] = "Failed to update in-car time for student ID: $student_id.";
    }
}


if (isset($_POST['deliver'])) {
    $student_id = $_POST['student_id'];
    $current_time = date('H:i:s');

    // Update query to set the current time for "student_in_car_to_host"
    $update_query = "UPDATE students SET student_delivered_to_homestay_home = :deliver_time WHERE ID = :student_id";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(':deliver_time', $current_time, PDO::PARAM_STR);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>
            $('#example').DataTable().ajax.reload(null, false);
        </script>";
    } else {
        $_SESSION['error'] = "Failed to update in-car time for student ID: $student_id.";
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

function displayStudents($pdo) {
    try {
        $current_date = date('Y-m-d');
        $subDriverId = $_SESSION['driverId']; 
        
        if (!$subDriverId) {
            throw new Exception("Sub Driver ID not found in session.");
        }
        
        $query = "SELECT * FROM students 
                WHERE Date = :current_date 
                AND driverId = :driverId 
                ORDER BY arr_time_dep_pu_time ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':current_date' => $current_date,
            ':driverId' => $subDriverId
        ]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($results)) {
            $rows = '';
            foreach ($results as $row) {
                $waiting_time = !empty($row['student_delivered_to_homestay_home']) ? 
                    $row['student_delivered_to_homestay_home'] : 'Deliver';
                $in_car_time = !empty($row['student_in_car_to_host']) ? 
                    $row['student_in_car_to_host'] : 'Pick Up';

                $rows .= "<tr style='text-align:center'>
                    <td>{$row['ID']}</td>
                    <td>
                        <form method='POST'>
                            <input type='hidden' name='student_id' value='{$row['ID']}'>
                            <button type='submit' name='update_in_car' class='btn btn-grd btn-grd-info'>
                                {$in_car_time}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method='POST'>
                            <input type='hidden' name='student_id' value='{$row['ID']}'>
                            <button type='submit' name='deliver' class='btn btn-grd btn-grd-info'>
                                {$waiting_time}
                            </button>
                        </form>
                    </td>
                    <td>{$row['Flight']}</td>
                    <td>{$row['arr_time_dep_pu_time']}</td>
                    <td>{$row['student_number']}</td>
                    <td>{$row['student_given_name']}</td>  
                </tr>";
            }
            return $rows;
        }
        return "<tr><td colspan='7' class='text-center'>No records found for today.</td></tr>";
        
    } catch (Exception $e) {
        error_log("Error in displayStudents: " . $e->getMessage());
        return "<tr><td colspan='7' class='text-center text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
}
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
                                            <div class="table-responsive">
                                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Pick Up</th>
                                                        <th>Delivered</th>
                                                        <th>Flight</th>
                                                        <th>Arrival Time</th>
                                                        <th>Student Number</th>
                                                        <th>Student Given Name</th>
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
                </div>
            </div>
        </div>
    </main>
    <!--end main wrapper-->

    <?php include 'partials/footer.php';?>