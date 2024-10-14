<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

$successNames = [];  // To store successfully added student names
$failedNames = [];   // To store failed student names

// If the 'Save' button is clicked, insert the data into the database
if (isset($_POST['save'])) {
    $selectedDate = $_POST['date'];  // Retrieve the selected date
    $sheetData = $_POST['data'];     // Retrieve the data from hidden inputs

    // Prepare the SQL insert query with placeholders
    $stmt = $pdo->prepare("INSERT INTO students (date, arr_time_dep_pu_time, flight_no, student_number, student_given_name, student_family_name, 
                                              host_given_name, host_family_name, client, waiting_for_student_at_airport, 
                                              student_in_car_to_host, student_delivered_to_homestay_home)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind and insert data row by row
    foreach ($sheetData as $index => $row) {
        // Skip the first row (headers)
        if ($index == 1) continue;

        // Assign each row's values
        $arrTimeDepPU = $row['A'];
        $flightNo = $row['B'];
        $studentNumber = $row['C'];
        $studentGivenName = $row['D'];
        $studentFamilyName = $row['E'];
        $hostGivenName = $row['F'];
        $hostFamilyName = $row['G'];
        $client = $row['H'];
        $waitingAtAirport = $row['I'];
        $inCarToHost = $row['J'];
        $deliveredToHomestay = $row['K'];

        // Execute the query with the current row's data
        if ($stmt->execute([$selectedDate, $arrTimeDepPU, $flightNo, $studentNumber, $studentGivenName, $studentFamilyName, 
                             $hostGivenName, $hostFamilyName, $client, $waitingAtAirport, $inCarToHost, $deliveredToHomestay])) {
            $successNames[] = $studentGivenName;  // Add to success list
        } else {
            $failedNames[] = $studentGivenName;   // Add to failed list
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
                                                        <div class="card-title d-flex align-items-center">
                                                            <h5 id="heading" class="mb-0 text-primary font-24 fw-bold">Select Date and Upload Excel File</h5>
                                                        </div>
                                                        <hr>
                                                        
                                                        <div class="col-md-6">
                                                            <label for="date" class="form-label">Select Date</label>
                                                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                                            <div class="invalid-feedback">
                                                                Please select a date.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mb-4">
                                                            <label for="date" class="form-label">Choose Excel File:</label>
                                                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx" >
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

                                                    // Store the selected date
                                                    $selectedDate = $_POST['date'];

                                                    // Display the data in a table format with hidden inputs
                                                    echo "<form method='post' action=''>";
                                                    echo "<input type='hidden' name='date' value='" . htmlspecialchars($selectedDate, ENT_QUOTES) . "'>";  // Pass the selected date to the next form
                                                    echo "<div class='col-md-12 text-center g-3 mt-5'>
                                                            <div class='d-flex justify-content-start mb-4 gap-3 flex-wrap'>
                                                                <button type='submit' name='save' value='save' class='btn btn-gradient-info fw-bold px-5'>Save</button>
                                                            </div>
                                                        </div>";
                                                    echo "<table id='example' class='table table-striped table-bordered' style='width:100%'>";
                                                    echo "<thead>";
                                                    echo "<tr>
                                                            <th>Arr Time Dep PU Time</th>
                                                            <th>Flight #</th>
                                                            <th>Student Number</th>
                                                            <th>Student Given Name</th>
                                                            <th>Student Family Name</th>
                                                            <th>Host Given Name</th>
                                                            <th>Host Family Name</th>
                                                            <th>Client</th>
                                                            <th>Waiting For Student At Airport</th>
                                                            <th>Student In A Car To Host Family Home</th>
                                                            <th>Student Delivered To Homestay Home</th>
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
    
