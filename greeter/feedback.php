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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = $_POST['student'];
    $feedback = $_POST['feedback'];
    
    $updateQuery = "UPDATE students SET feedback = :feedback WHERE student_number = :student_number";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        'feedback' => $feedback,
        'student_number' => $student_number
    ]);

    $_SESSION['success'] = "Feedback submitted successfully!";
    header("Location: feedback");
    exit();
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
                                        <form method="POST" id="absenceForm" class="row g-3">
                                            <div class="col-md-6">
                                                <label for="date" class="form-label">Select Date</label>
                                                <input type="date" name="date" id="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-6 mb-4">
                                                <label for="student" class="form-label">Select Absent Student</label>
                                                <select class="form-control" name="student" id="student" data-placeholder="Choose one student">
                                                    <option></option>
                                                </select>
                                            </div>

                                            <div class="col-md-12 mb-4">
                                                <label for="feedback" class="form-label">Feedback</label>
                                                <textarea name="feedback" class="form-control" placeholder="Enter feedback about the absence" rows="6"></textarea>
                                            </div>

                                            <div class="col-md-12 text-center g-3 mt-3">
                                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                    <button type="submit" class="btn btn-grd btn-grd-info px-5 fw-bold mt-4">Submit</button>
                                                </div>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#student').select2({
            theme: 'bootstrap-5',
            placeholder: "Choose a student"
        });

        // Handle date change event
        $('#date').change(function() {
            var selectedDate = $(this).val();
            
            // Clear current options
            $('#student').empty().append('<option></option>');
            
            // Fetch students for selected date using AJAX
            $.ajax({
                url: 'fetch/get_students.php',
                method: 'POST',
                data: { date: selectedDate },
                success: function(response) {
                    const students = JSON.parse(response);
                    students.forEach(function(student) {
                        $('#student').append(
                            $('<option>', {
                                value: student.student_number,
                                text: student.student_given_name + ' (' + student.student_number + ')'
                            })
                        );
                    });
                }
            });
        });
    });
</script>
