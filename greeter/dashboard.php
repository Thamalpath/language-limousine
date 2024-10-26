<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

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

    $driver_id = isset($_POST['driverId']) ? $_POST['driverId'] : null;

    if (!$driver_id) {
        error_log('Driver ID is invalid or not set.');
        return 'failure';
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
                return 'success';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                error_log('Failed to execute the driver assignment query.');
                return 'failure';
            }
        } catch (PDOException $e) {
            error_log('PDOException: ' . $e->getMessage());
            return 'failure';
        }
    } else {

        error_log('Empty selected IDs or invalid driver ID.');
    }
    return 'failure';
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

// Function to display students based on selected date
function displayStudents($pdo) {
    if (isset($_POST['search'])) {
        $selected_date = $_POST['date'];
        $query = "SELECT * FROM students WHERE Date = :Date AND driverId IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':Date', $selected_date, PDO::PARAM_STR);
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
        } else {
            return "<tr><td colspan='12'>No records found for the selected date</td></tr>";
        }
    }
    return '';
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
                                                    <label for="date" class="form-label fw-bold fs-6">Select the Date</label>
                                                    <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="addItem" class="form-label">&nbsp;</label>
                                                    <button type="submit" name="search" class="btn btn-grd btn-grd-info px-5 fw-bold mt-4">Search</button>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="date" class="form-label fw-bold fs-6">Select Driver</label>
                                                    <select class="form-select" name="driverId" id="driverId" required>
                                                        <option value="">-- Select Driver --</option>
                                                        <?php echo fetchDrivers($pdo); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-4">
                                                    <label for="assign" class="form-label">&nbsp;</label>
                                                    <button type="submit" name="assign" class="btn btn-grd btn-grd-info px-5 fw-bold mt-4">Assign</button>
                                                </div>
                                                <div class="table-responsive">
                                                <table id="example" class="table table-striped table-bordered" style="width:100%">
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
</main>

<?php include 'partials/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var assignmentStatus = "<?php echo $assignmentStatus; ?>";
        if (assignmentStatus === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Drivers assigned successfully!',
                confirmButtonText: 'OK'
            });
        } else if (assignmentStatus === 'failure') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to assign drivers. Please try again.',
                confirmButtonText: 'OK'
            });
        }
    });
</script>
