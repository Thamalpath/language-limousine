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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's an update operation
    if (!empty($_POST['student_id'])) {
        try {
            $sql = "UPDATE students SET 
                Date = :date,
                Trip = :trip,
                actual_arrival_time = :actual_arrival_time,
                arr_time_dep_pu_time = :arr_time_dep_pu_time,
                Flight = :flight,
                DI = :di,
                M_or_F = :m_or_f,
                student_number = :student_number,
                student_given_name = :student_given_name,
                student_family_name = :student_family_name,
                host_given_name = :host_given_name,
                host_family_name = :host_family_name,
                Phone = :phone,
                Address = :address,
                City = :city,
                School = :school,
                client = :client
                WHERE ID = :id";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':date' => $_POST['selected_date'],
                ':trip' => $_POST['Trip'],
                ':actual_arrival_time' => $_POST['actual_arrival_time'],
                ':arr_time_dep_pu_time' => $_POST['arr_time_dep_pu_time'],
                ':flight' => $_POST['Flight'],
                ':di' => $_POST['DI'],
                ':m_or_f' => $_POST['M_or_F'],
                ':student_number' => $_POST['student_number'],
                ':student_given_name' => $_POST['student_given_name'],
                ':student_family_name' => $_POST['student_family_name'],
                ':host_given_name' => $_POST['host_given_name'],
                ':host_family_name' => $_POST['host_family_name'],
                ':phone' => $_POST['Phone'],
                ':address' => $_POST['Address'],
                ':city' => $_POST['City'],
                ':school' => $_POST['School'],
                ':client' => $_POST['client'],
                ':id' => $_POST['student_id']
            ]);

            $_SESSION['success'] = "Student data updated successfully!";
            header("Location: add-student");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating student: " . $e->getMessage();
            header("Location: add-student");
            exit();
        }
    } else {
        try {
            $sql = "INSERT INTO students (
                Date, Trip, actual_arrival_time, arr_time_dep_pu_time, 
                Flight, DI, M_or_F, student_number, student_given_name, 
                student_family_name, host_given_name, host_family_name, 
                Phone, Address, City, School, client
            ) VALUES (
                :date, :trip, :actual_arrival_time, :arr_time_dep_pu_time,
                :flight, :di, :m_or_f, :student_number, :student_given_name,
                :student_family_name, :host_given_name, :host_family_name,
                :phone, :address, :city, :school, :client
            )";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':date' => $_POST['selected_date'],
                ':trip' => $_POST['Trip'],
                ':actual_arrival_time' => $_POST['actual_arrival_time'],
                ':arr_time_dep_pu_time' => $_POST['arr_time_dep_pu_time'],
                ':flight' => $_POST['Flight'],
                ':di' => $_POST['DI'],
                ':m_or_f' => $_POST['M_or_F'],
                ':student_number' => $_POST['student_number'],
                ':student_given_name' => $_POST['student_given_name'],
                ':student_family_name' => $_POST['student_family_name'],
                ':host_given_name' => $_POST['host_given_name'],
                ':host_family_name' => $_POST['host_family_name'],
                ':phone' => $_POST['Phone'],
                ':address' => $_POST['Address'],
                ':city' => $_POST['City'],
                ':school' => $_POST['School'],
                ':client' => $_POST['client']
            ]);

            $_SESSION['success'] = "Student data added successfully!";
            header("Location: add-student");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding student: " . $e->getMessage();
            header("Location: add-student");
            exit();
        }
    }
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
    header("Location: add-student");
    exit();
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
                                            <form method="POST" class="row g-3 needs-validation" novalidate>
                                                <input type="hidden" id="student_id" name="student_id" value=""> 
                                                
                                                <div class="col-md-3">
                                                    <label for="selected_date" class="form-label fw-bold fs-6">Select the Date</label>
                                                    <input type="date" name="selected_date" id="selected_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                    <div class="invalid-feedback">
                                                        Please select a date.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="Trip" class="form-label fw-bold fs-6">Trip</label>
                                                    <input type="text" name="Trip" id="Trip" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the trip.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="actual_arrival_time" class="form-label fw-bold fs-6">Actual Arrival Time</label>
                                                    <input type="text" name="actual_arrival_time" id="actual_arrival_time" class="form-control time-picker" value="">                                                
                                                </div>
                                                <div class="col-md-3 mb-4">
                                                    <label for="arr_time_dep_pu_time" class="form-label fw-bold fs-6">Arrival Time</label>
                                                    <input type="text" name="arr_time_dep_pu_time" id="arr_time_dep_pu_time" class="form-control time-picker" value="">                                                
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="Flight" class="form-label fw-bold fs-6">Flight</label>
                                                    <input type="text" name="Flight" id="Flight" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the flight.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="DI" class="form-label fw-bold fs-6">D or I</label>
                                                    <input type="text" name="DI" id="DI" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the D or I.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="M_or_F" class="form-label fw-bold fs-6">M or F</label>
                                                    <input type="text" name="M_or_F" id="M_or_F" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the M or F.
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-4">
                                                    <label for="student_number" class="form-label fw-bold fs-6">Student No</label>
                                                    <input type="text" name="student_number" id="student_number" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the student no.
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="student_given_name" class="form-label fw-bold fs-6">Student Given Name</label>
                                                    <input type="text" name="student_given_name" id="student_given_name" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the student name.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="student_family_name" class="form-label fw-bold fs-6">Student Family Name</label>
                                                    <input type="text" name="student_family_name" id="student_family_name" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the student family name.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="host_given_name" class="form-label fw-bold fs-6">Host Given Name</label>
                                                    <input type="text" name="host_given_name" id="host_given_name" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the host given name.
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-4">
                                                    <label for="host_family_name" class="form-label fw-bold fs-6">Host Family Name</label>
                                                    <input type="text" name="host_family_name" id="host_family_name" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the host family name.
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <label for="Phone" class="form-label fw-bold fs-6">Phone</label>
                                                    <input type="text" name="Phone" id="Phone" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the phone.
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="Address" class="form-label fw-bold fs-6">Address</label>
                                                    <input type="text" name="Address" id="Address" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the address.
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="City" class="form-label fw-bold fs-6">City</label>
                                                    <input type="text" name="City" id="City" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the city.
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="School" class="form-label fw-bold fs-6">School</label>
                                                    <input type="text" name="School" id="School" class="form-control" value="" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the school.
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="client" class="form-label fw-bold fs-6">client</label>
                                                    <select name="client" id="client" class="form-control" required>
                                                        <option value="">Select Client</option>
                                                        <?php
                                                        $stmt = $pdo->query("SELECT schoolID FROM school");
                                                        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach($schools as $school) {
                                                            echo "<option value='" . htmlspecialchars($school['schoolID']) . "'>" . htmlspecialchars($school['schoolID']) . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select a client.
                                                    </div>
                                                </div>                                            

                                                <div class="col-md-12 mt-5">
                                                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                        <button type="submit" id="submit" class="btn btn-gradient-info px-5 fw-bold" style="display: block;">Submit</button>
                                                        <button type="submit" id="update" class="btn btn-gradient-warning px-5 py-2 fw-bold" style="display: none;">Update</button>
                                                        <button type="reset" class="btn btn-gradient-dark px-5 fw-bold">Reset</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Date</th>
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
                                            <?php
                                                $stmt = $pdo->query("SELECT * FROM students ORDER BY Date DESC");
                                                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            <?php if (!empty($students)): ?>
                                                <?php foreach ($students as $index => $student): ?>
                                                    <tr data-id="<?php echo htmlspecialchars($student['ID']); ?>">
                                                        <td>
                                                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                                <a href="#" data-id="<?php echo $student['ID']; ?>" class="btn btn-gradient-danger px-4 delete-btn">Delete</a>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($student['Date']); ?></td>
                                                        <td><?php echo htmlspecialchars($student['Trip']); ?></td>
                                                        <td><?php echo htmlspecialchars($student['actual_arrival_time'] ?? ''); ?></td>
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
</main>

<?php include 'partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listener to table rows
    document.querySelectorAll('#example tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            
            // Fetch student data using AJAX
            fetch(`fetch/fetch_student.php?id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    // Set the hidden student ID field
                    document.getElementById('student_id').value = data.ID;
                    
                    // Populate form fields
                    document.getElementById('selected_date').value = data.Date;
                    document.getElementById('Trip').value = data.Trip;
                    document.getElementById('actual_arrival_time').value = data.actual_arrival_time;
                    document.getElementById('arr_time_dep_pu_time').value = data.arr_time_dep_pu_time;
                    document.getElementById('Flight').value = data.Flight;
                    document.getElementById('DI').value = data.DI;
                    document.getElementById('M_or_F').value = data.M_or_F;
                    document.getElementById('student_number').value = data.student_number;
                    document.getElementById('student_given_name').value = data.student_given_name;
                    document.getElementById('student_family_name').value = data.student_family_name;
                    document.getElementById('host_given_name').value = data.host_given_name;
                    document.getElementById('host_family_name').value = data.host_family_name;
                    document.getElementById('Phone').value = data.Phone;
                    document.getElementById('Address').value = data.Address;
                    document.getElementById('City').value = data.City;
                    document.getElementById('School').value = data.School;
                    document.getElementById('client').value = data.client;

                    // Show update button and hide submit button
                    document.getElementById('submit').style.display = 'none';
                    document.getElementById('update').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    notyf.error('Error fetching student data');
                });
        });
    });

    // Reset form handler
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        document.getElementById('student_id').value = '';
        document.getElementById('submit').style.display = 'block';
        document.getElementById('update').style.display = 'none';
    });
});
</script>
    
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
                    window.location.href = 'add-student?delete_id=' + deleteId;
                });

                document.getElementById('noButton').addEventListener('click', function() {
                    document.body.removeChild(confirmationDialog);
                    notyf.error('Delete canceled');
                });
            });
        });
    });
</script>