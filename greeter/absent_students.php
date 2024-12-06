<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');

require_once 'config/dbcon.php';

// Strict session validation
if (!isset($_SESSION['signed_in']) || $_SESSION['role'] !== 'Greeter') {
    header("Location: ./");
    exit();
}

// AJAX Request Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Validate CSRF token here if implemented
    
    if (isset($_POST['date'])) {
        getStudentsByDate($pdo, $_POST['date']);
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'save_feedback') {
        processFeedback($pdo);
        exit;
    }
}

function getStudentsByDate(PDO $pdo, string $date): void {
    $query = "SELECT student_number, student_given_name, feedback 
              FROM students 
              WHERE date = :date";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute(['date' => $date]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function processFeedback(PDO $pdo): void {
    $requiredFields = ['student_number', 'feedback', 'date'];
    
    // Validate required fields
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse('error', 'Missing required field: ' . $field);
            return;
        }
    }

    try {
        $pdo->beginTransaction();

        // Check if student exists
        $checkStmt = $pdo->prepare("SELECT ID FROM students 
                                   WHERE student_number = :student_number 
                                   AND date = :date");
                                   
        $checkStmt->execute([
            'student_number' => $_POST['student_number'],
            'date' => $_POST['date']
        ]);

        if ($checkStmt->fetch()) {
            // Update existing record
            $updateStmt = $pdo->prepare("UPDATE students 
                                       SET feedback = :feedback,
                                           absent = 1,
                                           updated_at = CURRENT_TIMESTAMP
                                       WHERE student_number = :student_number 
                                       AND date = :date");
                                       
            $updateStmt->execute([
                'feedback' => $_POST['feedback'],
                'student_number' => $_POST['student_number'],
                'date' => $_POST['date']
            ]);

            $pdo->commit();
            sendJsonResponse('success', 'Feedback recorded successfully');
        } else {
            throw new Exception('Student record not found');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        sendJsonResponse('error', $e->getMessage());
    }
}

function sendJsonResponse(string $status, string $message): void {
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
}
?>



<?php include 'partials/header.php'; ?>
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<header class="top-header">
    <?php include 'partials/navbar.php'; ?>
</header>

<?php include 'partials/sidebar.php'; ?>

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
                                            <select class="form-control" id="student-select" data-placeholder="Choose a student">
                                                <option></option>
                                                <?php
                                                $query = "SELECT student_number, student_given_name FROM students";
                                                $stmt = $pdo->query($query);
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
    const feedbackForm = {
        init: function() {
            this.initializeSelect2();
            this.bindEvents();
        },

        initializeSelect2: function() {
            $('#student-select').select2({
                theme: 'bootstrap-5',
                placeholder: "Choose a student"
            });
        },

        bindEvents: function() {
            $('#date-field').on('change', this.handleDateChange.bind(this));
            $('#submit-feedback').on('click', this.handleSubmit.bind(this));
        },

        handleDateChange: function(e) {
            const selectedDate = $(e.target).val();
            if (!selectedDate) return;

            $.ajax({
                url: 'absent_students.php',
                type: 'POST',
                data: { date: selectedDate },
                dataType: 'json'
            })
            .done(this.updateStudentList.bind(this))
            .fail(this.handleError.bind(this));
        },

        handleSubmit: function() {
            const formData = this.validateAndGetFormData();
            if (!formData) return;

            $.ajax({
                url: 'absent_students.php',
                type: 'POST',
                data: {
                    action: 'save_feedback',
                    ...formData
                },
                dataType: 'json'
            })
            .done(this.handleSubmitResponse.bind(this))
            .fail(this.handleError.bind(this));
        },

        validateAndGetFormData: function() {
            const data = {
                student_number: $('#student-select').val(),
                feedback: $('#feedback').val().trim(),
                date: $('#date-field').val()
            };

            if (!data.student_number || !data.feedback || !data.date) {
                alert("All fields are required");
                return null;
            }

            return data;
        },

        updateStudentList: function(students) {
            const select = $('#student-select');
            select.empty().append('<option></option>');
            
            students.forEach(student => {
                select.append(new Option(
                    `${student.student_given_name} (${student.student_number})`,
                    student.student_number
                ));
            });
        },

        handleSubmitResponse: function(response) {
            if (response.status === 'success') {
                this.resetForm();
                alert(response.message);
            } else {
                alert(response.message);
            }
        },

        resetForm: function() {
            $('#feedback').val('');
            $('#student-select').val(null).trigger('change');
        },

        handleError: function(xhr, status, error) {
            console.error("Request failed:", {xhr, status, error});
            alert("Operation failed. Please try again.");
        }
    };

    feedbackForm.init();
});
</script>
