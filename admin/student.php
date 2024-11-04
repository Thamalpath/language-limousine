<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');

include 'config/dbcon.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

// Retrieve school data from the school table
$schools = [];
try {
    $stmt_schools = $pdo->query("SELECT sch_id, username, schoolID FROM school");
    while ($row = $stmt_schools->fetch(PDO::FETCH_ASSOC)) {
        $schools[] = $row;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


$successNames = [];  // To store successfully added student names
$failedNames = [];

// If the 'Save' button is clicked, insert the data into the database
if (isset($_POST['save'])) {
    // $selectedDate = $_POST['date'];  // Retrieve the selected date
    $sheetData = $_POST['data'];
    $schoolID = $_POST['schoolID'] ?? '';     

    // Prepare the SQL insert query with placeholders
    $stmt = $pdo->prepare("INSERT INTO students (Date, Trip, actual_arrival_time, arr_time_dep_pu_time, Flight, DI, M_or_F, student_number, student_given_name, student_family_name, 
                                                host_given_name, host_family_name, Phone, Address, City, Special_instructions, Study_Permit, School, staff_member_assigned, client)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind and insert data row by row
    foreach ($sheetData as $index => $row) {
        // Skip the first row (headers)
        if ($index == 0) continue;
    
        if (empty($row['H'])) {  // Column H is student_number
            // Get the last auto-increment number for this school
            $lastStudentNumberQuery = "SELECT student_number FROM students WHERE student_number LIKE 'N/A/$schoolID/%' ORDER BY id DESC LIMIT 1";
            $lastStudentNumberStmt = $pdo->prepare($lastStudentNumberQuery);
            $lastStudentNumberStmt->execute();
            $lastStudentNumberRow = $lastStudentNumberStmt->fetch(PDO::FETCH_ASSOC);
    
            $lastAutoIncrement = 0;
            if ($lastStudentNumberRow) {
                $lastParts = explode('/', $lastStudentNumberRow['student_number']);
                $lastAutoIncrement = intval(end($lastParts));
            }
    
            // Generate new student number
            $lastAutoIncrement++;
            $row['H'] = "N/A/$schoolID/$lastAutoIncrement";
        }

        $selectedDate = !empty($row['B']) ? date('Y-m-d', strtotime($row['B'])) : '';
        $tripNumber = $row['A'] ?? '';
        $actualArrivalTime = !empty($row['C']) ? date('H:i:s', strtotime($row['C'])) : '';
        $arrTimeDepPuTime = !empty($row['D']) ? date('H:i:s', strtotime($row['D'])) : '';
        $flightNumber = $row['E'] ?? '';
        $dI = $row['F'] ?? '';
        $mOrF = $row['G'] ?? '';
        $studentNumber = $row['H'] ?? '';
        $studentGivenName = $row['I'] ?? '';
        $studentFamilyName = $row['J'] ?? '';
        $hostGivenName = $row['K'] ?? '';
        $hostFamilyName = $row['L'] ?? '';
        $phone = $row['M'] ?? '';
        $address = $row['N'] ?? '';
        $city = $row['O'] ?? '';
        $specialInstructions = $row['P'] ?? '';
        $studyPermit = $row['Q'] ?? '';
        $school = $row['R'] ?? '';
        $staffMemberAssigned = $row['S'] ?? '';

        // Execute the query with the current row's data
        if ($stmt->execute([$selectedDate, $tripNumber, $actualArrivalTime, $arrTimeDepPuTime, $flightNumber, $dI, $mOrF, $studentNumber, $studentGivenName, $studentFamilyName, 
                $hostGivenName, $hostFamilyName, $phone, $address, $city, $specialInstructions, $studyPermit, $school, $staffMemberAssigned, $schoolID])) {
            $successNames[] = $studentGivenName;
        } else {
            $failedNames[] = $studentGivenName;
        }
    }
}
?>

<?php include 'partials/header.php';?>
<link rel="stylesheet" href="assets/css/style.css" />
<style>
    .alert-success { color: green; }
    .alert-failed { color: red; }
</style>

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
		
		<!--start page wrapper -->
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
                                                    
                                                    <form method="POST" class="row g-3 needs-validation" enctype="multipart/form-data">
                                                        <input type="hidden" name="selected_school_id" id="selected_school_id">

                                                        <div class="card-title d-flex align-items-center">
                                                            <h5 id="heading" class="mb-0 text-primary font-24 fw-bold">Upload Student Data</h5>
                                                        </div>
                                                        <hr>
                                                        
                                                        <div class="col-md-4">
                                                            <label for="schoolID" class="form-label">School Name</label>
                                                            <select class="form-select" name="schoolID" id="schoolID" required>
                                                                <option value="">Select School</option>
                                                                <?php foreach ($schools as $school): ?>
                                                                    <option value="<?php echo htmlspecialchars($school['schoolID']); ?>" >
                                                                        <?php echo htmlspecialchars($school['schoolID']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Please select a school.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-4">
                                                            <label for="date" class="form-label">Choose Excel File:</label>
                                                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls">
                                                            <div class="invalid-feedback">
                                                                Please choose a excel file.
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 text-center g-3 mt-5">
                                                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                                <button type="submit" name="upload" value="Upload" class="btn btn-gradient-info fw-bold px-5">Upload</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
							</div>

                            <!-- Display Success/Failure Messages -->
                            <?php if (!empty($successNames)): ?>
                                <div class="alert-success">
                                    <strong>Successfully Added Students:</strong>
                                    <ul>
                                        <?php foreach ($successNames as $name): ?>
                                            <li><?php echo htmlspecialchars($name, ENT_QUOTES); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($failedNames)): ?>
                                <div class="alert-failed">
                                    <strong>Failed to Add Students:</strong>
                                    <ul>
                                        <?php foreach ($failedNames as $name): ?>
                                            <li><?php echo htmlspecialchars($name, ENT_QUOTES); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <?php
                                        // Check if the form is submitted for uploading the Excel file
                                        if (isset($_POST['upload'])) {
                                            $file = $_FILES['excel_file']['tmp_name'];

                                            // Verify the file upload
                                            if ($file && !empty($file)) {
                                                // Load the spreadsheet file
                                                try {
                                                    $spreadsheet = IOFactory::load($file);
                                                    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                                                    // Display the data in a table format with hidden inputs
                                                    echo "<form method='post' action=''>";
                                                    echo "<input type='hidden' name='schoolID' value='" . htmlspecialchars($_POST['selected_school_id'], ENT_QUOTES) . "'>";
                                                      // Pass the selected date to the next form
                                                    echo "<div class='col-md-12 text-center g-3 mt-5'>
                                                            <div class='d-flex justify-content-start mb-4 gap-3 flex-wrap'>
                                                                <button type='submit' name='save' value='save' class='btn btn-gradient-info fw-bold px-5'>Save</button>
                                                            </div>
                                                        </div>";
                                                    echo "<table id='example' class='table table-striped table-bordered' style='width:100%'>";
                                                    echo "<thead>";
                                                    echo "<tr>
                                                            <th>Date</th>
                                                            <th>Trip</th>
                                                            <th>Actual Arrival Time</th>            
                                                            <th>Arr Time Dep PU Time</th>
                                                            <th>Flight</th>
                                                            <th>D/I</th>
                                                            <th>M or F</th>
                                                            <th>Student Number</th>
                                                            <th>Student Given Name</th>
                                                            <th>Student Family Name</th>
                                                            <th>Host Given Name</th>
                                                            <th>Host Family Name</th>
                                                            <th>Phone</th>
                                                            <th>Address</th>
                                                            <th>City</th>
                                                            <th>Special Instructions</th>
                                                            <th>Study Permit</th>
                                                            <th>School</th>
                                                            <th>Staff Member Assigned</th>
                                                        </tr>";
                                                    echo "</thead>";

                                                    foreach ($sheetData as $index => $row) {
                                                        // Skip the first row (headers)
                                                        if ($index == 1) continue;

                                                        // Check if the row is empty (all cells are empty)
                                                        if (array_filter($row)) {  // array_filter removes empty values, so this checks if the row has any data
                                                            echo "<tr>";
                                                            foreach ($row as $key => $cell) {
                                                                echo "<td>" . htmlspecialchars($cell, ENT_QUOTES) . "</td>";
                                                                echo "<input type='hidden' name='data[$index][$key]' value='" . htmlspecialchars($cell, ENT_QUOTES) . "'>";
                                                            }
                                                            echo "</tr>";
                                                        }
                                                    }
                                                    echo "</table>";
                                                    echo "</form>";

                                                } catch (Exception $e) {
                                                    echo "Error loading file: " . $e->getMessage();
                                                }
                                            } else {
                                                echo "Please select a valid Excel file.";
                                            }
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
		</div>
		<!--end page wrapper -->

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>
    
    <script>
        document.getElementById('schoolID').addEventListener('change', function() {
            document.getElementById('selected_school_id').value = this.value;
        });
    </script>