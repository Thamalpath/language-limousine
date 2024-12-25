<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');
include 'config/dbcon.php';

// Check user session
if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'Greeter') {
    header("Location: ./");
    exit();
}

// Initialize assignment status
$assignmentStatus = '';

// Handle form submission for assigning drivers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign'])) {

        $assignmentStatus = assignDrivers($pdo);
    }
}

function assignDrivers($pdo) {
    // Assign based on whichever driverId is filled out
    $driver_id = !empty($_POST['driverId']) ? $_POST['driverId'] : $_POST['subDriverId'];

    if (!$driver_id) {
        $_SESSION['error'] = "Driver ID is invalid or not set.";
        header("Location: dashboard");
        exit();
    }

    $selected_ids = $_POST['selected_row'] ?? [];

    error_log('Driver ID: ' . $driver_id);
    error_log('Selected IDs: ' . implode(', ', $selected_ids));

    if (!empty($selected_ids) && !empty($driver_id)) {
        $ids_placeholder = implode(',', array_fill(0, count($selected_ids), '?'));
        $update_query = "UPDATE students SET driverId = ? WHERE ID IN ($ids_placeholder)";
        
        try {
            $stmt = $pdo->prepare($update_query);
            $params = array_merge([$driver_id], $selected_ids);
            if ($stmt->execute($params)) {
                $_SESSION['success'] = "Drivers assigned successfully!";
                header("Location: dashboard");
                exit();
            } else {
                $_SESSION['error'] = "Failed to execute the driver assignment query.";
                header("Location: dashboard");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: PDOException: " . $e->getMessage();
            header("Location: dashboard");
            exit();        }
    } else {
        $_SESSION['error'] = "Empty selected IDs or invalid driver ID.";
        header("Location: dashboard");
        exit();
    }
    $_SESSION['error'] = "Failed to assign drivers. Please try again.";
    header("Location: dashboard");
    exit();
}


// Function to fetch driver options
function fetchDrivers($pdo) {
    $driver_query = "SELECT driverId FROM drivers WHERE status != 'offduty'";
    $driver_stmt = $pdo->prepare($driver_query);
    $driver_stmt->execute();
    $drivers = $driver_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($drivers)) {
        return '<option value="">No available drivers</option>';
    }

    $options = '';
    foreach ($drivers as $driver) {
        $options .= "<option value='{$driver['driverId']}'>{$driver['driverId']}</option>";
    }
    return $options;
}

function fetchsubDrivers($pdo) {
    $driver_query = "SELECT driverId FROM `sub-drivers` WHERE status != 'offduty'";
    $driver_stmt = $pdo->prepare($driver_query);
    $driver_stmt->execute();
    $drivers = $driver_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($drivers)) {
        return '<option value="">No available drivers</option>';
    }

    $options = '';
    foreach ($drivers as $driver) {
        $options .= "<option value='{$driver['driverId']}'>{$driver['driverId']}</option>";
    }
    return $options;
}

// Function to display students for current date
function displayStudents($pdo) {
    $current_date = date('Y-m-d');
    $query = "SELECT * FROM students WHERE Date = :Date AND driverId IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':Date', $current_date, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($results)) {
        $rows = '';
        foreach ($results as $row) {
            $rows .= "<tr style='text-align:center'>
                        <td><input type='checkbox' name='selected_row[]' value='{$row['ID']}'></td>
                        <td>{$row['ID']}</td>
                        <td>{$row['arr_time_dep_pu_time']}</td>
                        <td>{$row['Flight']}</td>
                        <td>{$row['DI']}</td>
                        <td>{$row['student_number']}</td>
                        <td>{$row['student_given_name']}</td>
                        <td>{$row['host_given_name']}</td>
                        <td>{$row['Phone']}</td>
                        <td>{$row['Address']}</td>
                        <td>{$row['City']}</td>
                        <td>{$row['School']}</td>
                    </tr>";
        }
        return $rows;
    }
    return "<tr><td colspan='12'>No records found for today</td></tr>";
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
                                            <form method="POST" class="row g-3">
                                                <div class="col-md-3">
                                                    <label for="driverId" class="form-label fw-bold fs-6">Select Driver</label>
                                                    <select class="form-select" name="driverId" id="driverId">
                                                        <option value="">-- Select Driver --</option>
                                                        <?php echo fetchDrivers($pdo); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="subDriverId" class="form-label fw-bold fs-6">Select Sub Driver</label>
                                                    <select class="form-select" name="subDriverId" id="subDriverId">
                                                        <option value="">-- Select Sub Driver --</option>
                                                        <?php echo fetchsubDrivers($pdo); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-4 pt-2">
                                                    <label for="assign" class="form-label">&nbsp;</label>
                                                    <button type="submit" name="assign" class="btn btn-grd btn-grd-info px-5 fw-bold mt-4">Assign</button>
                                                </div>
                                                <div class="table-responsive">
                                                    <table id="assignDriver" class="table table-striped table-bordered" style="width:100%">
                                                        <!-- Table headers remain the same -->
                                                        <thead>
                                                            <tr>
                                                                <th>Pick</th>
                                                                <th>ID</th>
                                                                <th>Arrival Time</th>
                                                                <th>Flight</th>
                                                                <th>D or I</th>
                                                                <th>Student Number</th>
                                                                <th>Student Given Name</th>
                                                                <th>Host Given Name</th>
                                                                <th>Phone Numbers</th>
                                                                <th>Address</th>
                                                                <th>City</th>
                                                                <th>School</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php echo displayStudents($pdo); ?>
                                                        </tbody>
                                                    </table>
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
</main>

<?php include 'partials/footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#assignDriver').DataTable({
            pageLength: 100,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[1, 'asc']],
        });
    });
</script>
