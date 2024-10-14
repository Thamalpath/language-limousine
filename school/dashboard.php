<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'School' || !isset($_SESSION['school_id'])) {
    header("Location: ./");
    exit();
}

$schoolId = $_SESSION['school_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
    $fileName = $_FILES['excel_file']['tmp_name'];

    if (!isset($_POST['selected_date']) || empty($_POST['selected_date'])) {
      $_SESSION['error'] = 'Error: Please select a date.';
      header('Location: dashboard');
      exit;
    }

    $selectedDate = $_POST['selected_date'];
    require 'vendor/autoload.php';

    $spreadsheet = IOFactory::load($fileName);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();

    // Prepare SQL for checking if student exists
    $checkSql = "SELECT COUNT(*) FROM students WHERE student_number = ? AND Date = ?";
    $checkStmt = $pdo->prepare($checkSql);

    // Prepare SQL for insert or update
    $sqlInsert = "INSERT INTO students (Date, Trip, actual_arrival_time, arr_time_dep_pu_time, Flight, DI, M_or_F, student_number, student_given_name, student_family_name, host_given_name, host_family_name, Phone, Address, City, Special_instructions, Study_Permit, School, staff_member_assigned, client) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);

    $sqlUpdate = "UPDATE students SET Trip = ?, actual_arrival_time = ?, arr_time_dep_pu_time = ?, Flight = ?, DI = ?, M_or_F = ?, student_given_name = ?, student_family_name = ?, host_given_name = ?, host_family_name = ?, Phone = ?, Address = ?, City = ?, Special_instructions = ?, Study_Permit = ?, School = ?, staff_member_assigned = ?, client = ? WHERE student_number = ? AND Date = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);

    // Process each row
    for ($row = 2; $row <= $highestRow; $row++) {
      $tripNumber = $sheet->getCell("A{$row}")->getValue();
      $actualArrivalTime = date('H:i:s', strtotime($sheet->getCell("B{$row}")->getFormattedValue()));
      $arrTimeDepPuTime = date('H:i:s', strtotime($sheet->getCell("C{$row}")->getFormattedValue()));
      $flightNumber = $sheet->getCell("D{$row}")->getValue();
      $dI = $sheet->getCell("E{$row}")->getValue();
      $mOrF = $sheet->getCell("F{$row}")->getValue();
      $studentNumber = $sheet->getCell("G{$row}")->getValue();
      $studentGivenName = $sheet->getCell("H{$row}")->getValue();
      $studentFamilyName = $sheet->getCell("I{$row}")->getValue();
      $hostGivenName = $sheet->getCell("J{$row}")->getValue();
      $hostFamilyName = $sheet->getCell("K{$row}")->getValue();
      $phone = $sheet->getCell("L{$row}")->getValue();
      $address = $sheet->getCell("M{$row}")->getValue();
      $city = $sheet->getCell("N{$row}")->getValue();
      $specialInstructions = $sheet->getCell("O{$row}")->getValue();
      $studyPermit = $sheet->getCell("P{$row}")->getValue();
      $staffMemberAssigned = $sheet->getCell("Q{$row}")->getValue();

      // Check if the student record exists
      $checkStmt->execute([$studentNumber, $selectedDate]);
      $exists = $checkStmt->fetchColumn();

      if ($exists) {
        // Update existing record
        $stmtUpdate->execute([
          $tripNumber, $actualArrivalTime, $arrTimeDepPuTime, $flightNumber,
          $dI, $mOrF, $studentGivenName, $studentFamilyName, $hostGivenName, 
          $hostFamilyName, $phone, $address, $city, $specialInstructions, 
          $studyPermit, $schoolId, $staffMemberAssigned, $schoolId, $studentNumber, $selectedDate
        ]);
      } else {
        // Insert new record
        $stmtInsert->execute([
          $selectedDate, $tripNumber, $actualArrivalTime, $arrTimeDepPuTime, 
          $flightNumber, $dI, $mOrF, $studentNumber, $studentGivenName, 
          $studentFamilyName, $hostGivenName, $hostFamilyName, $phone, 
          $address, $city, $specialInstructions, $studyPermit, $schoolId, 
          $staffMemberAssigned, $schoolId
        ]);
      }
    }
    $_SESSION['success'] = 'Success: Data uploaded successfully!';
    header('Location: dashboard');
    exit;
  } else {
    $_SESSION['error'] = 'Error: No file uploaded or an error occurred.';
    header('Location: dashboard');
    exit;
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
                      <h2 class="mt-3 mb-5 fw-bold">Upload Student Data</h2>
                      <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-6">
                          <label for="selected_date" class="form-label fw-bold fs-6">Select the Date</label>
                          <input type="date" class="form-control" name="selected_date" id="selected_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                          <label for="excel_file" class="form-label fw-bold fs-6">Choose Excel File:</label>
                          <input type="file" class="form-control" name="excel_file" id="excel_file" accept=".xlsx, .xls">
                        </div>

                        <div class="col-md-12 text-center g-3 mt-5">
                          <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button type="submit" name="submit" class="btn btn-grd btn-grd-info px-5 fw-bold mt-4">Submit</button>
                            <button type="reset" class="btn btn-grd btn-grd-royal px-5 fw-bold mt-4">Reset</button>
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
  <!--end main wrapper-->

  <?php include 'partials/footer.php';?>