<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';

// User session check
if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'Greeter') {
    header("Location: ./");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Fetch students based on selected date
    if (isset($_POST['date'])) {
        $selected_date = $_POST['date'];
        $query = "SELECT student_number, student_given_name FROM students WHERE date = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$selected_date]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($students);
        exit;
    }

    // Save feedback in database
    if (isset($_POST['action']) && $_POST['action'] === 'save_feedback') {
        $student_number = $_POST['student_number'];
        $feedback = $_POST['feedback'];
        $date = $_POST['date'];
        $query = "UPDATE students SET feedback = ? WHERE student_number = ? AND date = ?";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute([$feedback, $student_number, $date])) {
            echo json_encode(['status' => "success"]);
        } else {
            echo json_encode(['status' => "error", 'errorInfo' => $stmt->errorInfo()]);
        }
        exit;
    }
}
?>



<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

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
                                        <!-- Date Picker -->
                                        <div class="mb-4">
                                            <label for="date-field" class="form-label">Select Date</label>
                                            <input type="date" id="date-field" class="form-control">
                                        </div>

                                        <!-- Student Dropdown -->
                                        <div class="mb-4">
                                            <label for="student-select" class="form-label">Select Absent Student</label>
                                            <select class="form-control" id="student-select" data-placeholder="Choose one student">
                                                <option></option>
                                                    <?php
                                                    // Query to fetch student data
                                                    $query = "SELECT student_number, student_given_name FROM students";
                                                    $stmt = $pdo->query($query); // assuming PDO connection

                                                    // Loop through each student and add them as options in the dropdown
                                                    while ($row = $stmt->fetch()) {
                                                        echo "<option value='" . htmlspecialchars($row['student_number']) . "'>" 
                                                            . htmlspecialchars($row['student_given_name']) . " (" . htmlspecialchars($row['student_number']) . ")"
                                                            . "</option>";
                                                    }
                                                    ?>
                                            </select>
                                        </div>

                                        <!-- Feedback Text Area -->
                                        <div class="mb-4">
                                            <label for="feedback" class="form-label">Feedback</label>
                                            <textarea id="feedback" class="form-control" placeholder="Enter feedback about the absence"></textarea>
                                        </div>

                                        <!-- Submit Button -->
                                        <button id="submit-feedback" class="btn btn-primary">Submit Feedback</button>
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
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#student-select').select2({
        theme: 'bootstrap-5',
        placeholder: "Choose a student"
    });

    // Fetch students on date change
    $('#date-field').change(function() {
        const selectedDate = $(this).val();
        if (selectedDate) {
            $.ajax({
                url: 'absent_students.php', // Replace with the actual PHP filename
                type: 'POST',
                data: { date: selectedDate },
                dataType: 'json',
                success: function(data) {
                    $('#student-select').empty().append('<option></option>');
                    data.forEach(student => {
                        $('#student-select').append(new Option(`${student.student_given_name} (${student.student_number})`, student.student_number));
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching students: ", error);
                    alert("Failed to retrieve students.");
                }
            });
        }
    });

    // Submit feedback
    $('#submit-feedback').click(function() {
        const studentNumber = $('#student-select').val();
        const feedback = $('#feedback').val();
        const date = $('#date-field').val();

        if (studentNumber && feedback) {
            $.ajax({
                url: 'absent_students.php', // Replace with the actual PHP filename
                type: 'POST',
                data: {
                    student_number: studentNumber,
                    feedback: feedback,
                    date: date,
                    action: 'save_feedback'
                },
                success: function(response) {
                    if (response.status === "success") {
                        alert("Feedback submitted successfully!");
                        $('#feedback').val('');
                    } else {
                        alert("Failed to submit feedback.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error submitting feedback: ", xhr.responseText, error);
                    alert("An error occurred. Please check the console for details.");
                }

            });
        } else {
            alert("Please select a student and enter feedback.");
        }
    });
});


</script>
<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="assets/js/main.js"></script>
