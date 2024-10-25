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
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

function displayStudents($pdo) {
    if (isset($_POST['search'])) {
        $selected_date = $_POST['date'];
        $query = "SELECT * FROM students WHERE Date = :Date AND student_in_car_to_host IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':Date', $selected_date, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($results)) {
            $rows = '';
            foreach ($results as $row) {
                $time_button = !empty($row['waiting_for_student_at_airport']) ? $row['waiting_for_student_at_airport'] : 'Time';

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
            return "<tr><td colspan='12'>No records found for the selected date.</td></tr>";
        }
    }
    return '';
}

?>

<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />

    <header class="top-header">
        <?php include 'partials/navbar.php';?>
    </header>

    <?php include 'partials/sidebar.php';?>

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

