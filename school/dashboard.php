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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
      $fileName = $_FILES['excel_file']['tmp_name'];
      require 'vendor/autoload.php';

      $spreadsheet = IOFactory::load($fileName);
      $sheet = $spreadsheet->getActiveSheet();
      $highestRow = $sheet->getHighestRow();

      // Get last auto-increment for student numbers
      $lastStudentNumberQuery = "SELECT student_number FROM students WHERE student_number LIKE 'N/A/$schoolId/%' ORDER BY id DESC LIMIT 1";
      $lastStudentNumberStmt = $pdo->prepare($lastStudentNumberQuery);
      $lastStudentNumberStmt->execute();
      $lastStudentNumberRow = $lastStudentNumberStmt->fetch(PDO::FETCH_ASSOC);
      
      $lastAutoIncrement = 0;
      if ($lastStudentNumberRow) {
          $lastParts = explode('/', $lastStudentNumberRow['student_number']);
          $lastAutoIncrement = intval(end($lastParts));
      }

      $previewData = [];
      $now = new DateTime();
      $cutoffDate = (clone $now)->modify('+72 hours');

      for ($row = 2; $row <= $highestRow; $row++) {

          $dateValue = $sheet->getCell("B{$row}")->getFormattedValue();
          $dateObject = DateTime::createFromFormat('d.m.Y', $dateValue);
          $formattedDate = $dateObject ? $dateObject->format('Y-m-d') : null;

          // Get all cell values
          $rowData = [
              'date' => $formattedDate,
              'tripNumber' => $sheet->getCell("A{$row}")->getValue(),
              'actualArrivalTime' => $sheet->getCell("C{$row}")->getFormattedValue(),
              'arrTimeDepPuTime' => $sheet->getCell("D{$row}")->getFormattedValue(),
              'flightNumber' => $sheet->getCell("E{$row}")->getValue(),
              'dI' => $sheet->getCell("F{$row}")->getValue(),
              'mOrF' => $sheet->getCell("G{$row}")->getValue(),
              'studentNumber' => $sheet->getCell("H{$row}")->getValue(),
              'studentGivenName' => $sheet->getCell("I{$row}")->getValue(),
              'studentFamilyName' => $sheet->getCell("J{$row}")->getValue(),
              'hostGivenName' => $sheet->getCell("K{$row}")->getValue(),
              'hostFamilyName' => $sheet->getCell("L{$row}")->getValue(),
              'phone' => $sheet->getCell("M{$row}")->getValue(),
              'address' => $sheet->getCell("N{$row}")->getValue(),
              'city' => $sheet->getCell("O{$row}")->getValue(),
              'specialInstructions' => $sheet->getCell("P{$row}")->getValue(),
              'studyPermit' => $sheet->getCell("Q{$row}")->getValue(),
              'school' => $sheet->getCell("R{$row}")->getValue(),
              'staffMemberAssigned' => $sheet->getCell("S{$row}")->getValue(),
          ];

          // Generate student number if empty
          if (empty($rowData['studentNumber'])) {
              $lastAutoIncrement++;
              $rowData['studentNumber'] = "N/A/$schoolId/$lastAutoIncrement";
          }

          // Check if date is within 72 hours
          $rowData['isWithin72Hours'] = ($dateObject && $dateObject <= $cutoffDate);

          $previewData[] = $rowData;
      }

      $_SESSION['preview_data'] = $previewData;
      header("Location: dashboard");
      exit();
  }
}

if (isset($_POST['confirm_upload'])) {
  $previewData = $_SESSION['preview_data'] ?? [];
  foreach ($previewData as $record) {
    if ($record['isWithin72Hours'] || empty($record['date'])) {
        continue;
    }
  $checkSql = "SELECT COUNT(*) FROM students WHERE student_number = ? AND Date = ?";
  $checkStmt = $pdo->prepare($checkSql);

  $sqlInsert = "INSERT INTO students (Date, Trip, actual_arrival_time, arr_time_dep_pu_time, Flight, DI, M_or_F, student_number, student_given_name, student_family_name, host_given_name, host_family_name, Phone, Address, City, Special_instructions, Study_Permit, School, staff_member_assigned, client) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmtInsert = $pdo->prepare($sqlInsert);

  foreach ($previewData as $record) {
      // Skip records within 72 hours
      if ($record['isWithin72Hours']) {
          continue;
      }

          // Insert new record
          $stmtInsert->execute([
              $record['date'], $record['tripNumber'], $record['actualArrivalTime'], 
              $record['arrTimeDepPuTime'], $record['flightNumber'], 
              $record['dI'], $record['mOrF'], $record['studentNumber'], 
              $record['studentGivenName'], $record['studentFamilyName'], 
              $record['hostGivenName'], $record['hostFamilyName'], 
              $record['phone'], $record['address'], $record['city'], 
              $record['specialInstructions'], $record['studyPermit'], 
              $record['school'], $record['staffMemberAssigned'], $schoolId
          ]);
      
  }
  unset($_SESSION['preview_data']);
  $_SESSION['success'] = 'Success: Data uploaded successfully!';
  header("Location: dashboard");
  exit();
}
}

// Add this at the beginning of your POST handling code
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_preview') {
    unset($_SESSION['preview_data']);
    exit();
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
                      <form form id="uploadForm" method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-grow-1">
                            <label for="excel_file" class="form-label fw-bold fs-6">Choose Excel File:</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls" onchange="triggerPreview()">
                          </div>
                          <div class="d-flex gap-2 align-items-end mt-4">
                            <button type="submit" name="submit" class="btn btn-grd btn-grd-info px-5 fw-bold">Submit</button>
                            <button type="button" class="btn btn-grd btn-grd-royal px-5 fw-bold" onclick="resetPreviewData()">Reset</button>
                          </div>
                        </div>
                      </form>
                      <?php if(isset($_SESSION['preview_data']) && !empty($_SESSION['preview_data'])): ?>
                        <h2 class="mt-5">Preview Records</h2>
                        <form method="POST">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Trip</th>
                                            <th>Date</th>
                                            <th>Flight Number</th>
                                            <th>Student Name</th>
                                            <th>Student Number</th>
                                            <th>Host Name</th>
                                            <th>City</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['preview_data'] as $row): ?>
                                        <tr <?php echo $row['isWithin72Hours'] ? 'class="table-danger"' : ''; ?>>
                                            <td><?= htmlspecialchars($row['tripNumber']) ?></td>
                                            <td><?= htmlspecialchars($row['date']) ?></td>
                                            <td><?= htmlspecialchars($row['flightNumber']) ?></td>
                                            <td><?= htmlspecialchars($row['studentGivenName'] . ' ' . $row['studentFamilyName']) ?></td>
                                            <td><?= htmlspecialchars($row['studentNumber']) ?></td>
                                            <td><?= htmlspecialchars($row['hostGivenName'] . ' ' . $row['hostFamilyName']) ?></td>
                                            <td><?= htmlspecialchars($row['city']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-4">
                              <button type="submit" name="confirm_upload" class="btn btn-grd btn-grd-success px-5 fw-bold">Confirm Upload</button>
                            </div>
                        </form>
                    <?php endif; ?>
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

  <script>
    function resetPreviewData() {
      // Clear the file input
      document.getElementById('excel_file').value = '';
      
      // Clear preview data from session using AJAX
      fetch('dashboard.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=reset_preview'
      })
      .then(() => {
          // Reload the page to clear the preview table
          window.location.reload();
      });
    }
  </script>

