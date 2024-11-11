<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

// Fetch on-duty drivers
$stmt_drivers = $pdo->prepare("SELECT driver_id, driverId, username FROM drivers WHERE status = 'onduty'");
$stmt_drivers->execute();
$onduty_drivers = $stmt_drivers->fetchAll(PDO::FETCH_ASSOC);

// Fetch on-duty sub-drivers
$stmt_subdrivers = $pdo->prepare("SELECT subdri_id, driverId, username FROM `sub-drivers` WHERE status = 'onduty'");
$stmt_subdrivers->execute();
$onduty_subdrivers = $stmt_subdrivers->fetchAll(PDO::FETCH_ASSOC);

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
                                                    <form id="printForm" class="row g-3 needs-validation" method="POST" novalidate>
                                                        <div class="card-title d-flex align-items-center">
                                                            <h5 class="mb-0 text-primary font-24 fw-bold">Print Student Data</h5>
                                                        </div>
                                                        <hr>

                                                        <div class="col-md-3">
                                                            <label for="driverSelect" class="form-label fw-bold font-18">Select Driver</label>
                                                            <select id="driverSelect" class="form-select me-2">
                                                                <option value="">Select Driver</option>
                                                                <?php foreach ($onduty_drivers as $driver): ?>
                                                                    <option value="<?php echo $driver['driverId']; ?>"><?php echo htmlspecialchars($driver['username']); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mt-5">
                                                            <label for="print" class="form-label">&nbsp;</label>
                                                            <button type="button" id="printDriverBtn" name="print" class="btn btn-gradient-info fw-bold px-5">Print</button>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label for="subDriverSelect" class="form-label fw-bold font-18">Select Sub Driver</label>
                                                            <select id="subDriverSelect" class="form-select me-2">
                                                                <option value="">Select Sub-Driver</option>
                                                                <?php foreach ($onduty_subdrivers as $subdriver): ?>
                                                                    <option value="<?php echo $subdriver['driverId']; ?>"><?php echo htmlspecialchars($subdriver['username']); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mt-5">
                                                            <label for="print" class="form-label">&nbsp;</label>
                                                            <button type="button" id="printSubDriverBtn" name="print" class="btn btn-gradient-info fw-bold px-5">Print</button>
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
            </div>
		</div>
		<!--end page wrapper -->

		<?php include 'partials/endarea.php';?>
	</div>

	<?php include 'partials/footer.php';?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const printDriverBtn = document.getElementById('printDriverBtn');
            const printSubDriverBtn = document.getElementById('printSubDriverBtn');

            printDriverBtn.addEventListener('click', function() {
                const selectedDriverId = document.getElementById('driverSelect').value;
                const selectedDriverUsername = document.getElementById('driverSelect').options[document.getElementById('driverSelect').selectedIndex].text;
                
                if (!selectedDriverId) {
                    notyf.error('Please select a driver');
                    return;
                }

                // Make AJAX call to fetch student data
                fetch('fetch/get_student_data_by_driver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `driverId=${selectedDriverId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let printWindow = window.open('', '_blank');
                        
                        // Create table headers based on your student data fields
                        let tableHeaders = `
                            <tr>
                                <th>#</th>
                                <th>Flight</th>
                                <th>Student No</th>
                                <th>Student Name</th>
                                <th>Host Name</th>
                                <th>Address</th>
                                <th>Client</th>
                                <th>Waiting</th>
                                <th>Pick Up</th>
                                <th>Delivered</th>
                            </tr>`;

                        // Create table rows from student data
                        let tableRows = data.students.map((student, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${student.Flight}</td>
                                <td>${student.student_number}</td>
                                <td>${student.student_given_name} - ${student.student_family_name}</td>
                                <td>${student.host_given_name} - ${student.host_family_name}</td>
                                <td>${student.Address} , ${student.City}</td>
                                <td>${student.client}</td>
                                <td>${student.waiting_for_student_at_airport || ''}</td>
                                <td>${student.student_in_car_to_host || ''}</td>
                                <td>${student.student_delivered_to_homestay_home || ''}</td>
                            </tr>
                        `).join('');

                        let printContent = `
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Student Data Report</title>
                                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
                                <style>
                                    body { font-family: Arial, sans-serif; }
                                    .container { max-width: 100%; margin: 20px; }
                                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                    table, th, td { border: 1px solid black; }
                                    th, td { padding: 8px; text-align: left; font-size: 12px; }
                                    @media print {
                                        .no-print { display: none; }
                                        body { margin: 0; padding: 15px; }
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <h1 class="text-center fw-bold">Transport Report</h1>

                                    <h3 class="mt-5 mb-4">Driver: ${selectedDriverUsername} - ${data.students[0].Date}</h3>

                                    <table>
                                        <thead>${tableHeaders}</thead>
                                        <tbody>${tableRows}</tbody>
                                    </table>
                                    <p class="mt-3">Total Records: ${data.students.length}</p>
                                </div>
                            </body>
                            </html>`;

                        printWindow.document.write(printContent);
                        printWindow.document.close();
                        printWindow.print();
                        
                        printWindow.onafterprint = function() {
                            printWindow.close();
                        };
                    } else {
                        notyf.error(data.message || 'Failed to fetch student data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notyf.error('An error occurred while fetching data');
                });
            });

            printSubDriverBtn.addEventListener('click', function() {
                const selectedDriverId = document.getElementById('subDriverSelect').value;
                const selectedSubDriverUsername = document.getElementById('subDriverSelect').options[document.getElementById('subDriverSelect').selectedIndex].text;
        
                if (!selectedDriverId) {
                    notyf.error('Please select a sub driver');
                    return;
                }

                // Make AJAX call to fetch student data
                fetch('fetch/get_student_data_by_sub_driver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `driverId=${selectedDriverId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let printWindow = window.open('', '_blank');
                        
                        // Create table headers based on your student data fields
                        let tableHeaders = `
                            <tr>
                                <th>#</th>
                                <th>Flight</th>
                                <th>Student No</th>
                                <th>Student Name</th>
                                <th>Host Name</th>
                                <th>Address</th>
                                <th>Client</th>
                                <th>Waiting</th>
                                <th>Pick Up</th>
                                <th>Delivered</th>
                            </tr>`;

                        // Create table rows from student data
                        let tableRows = data.students.map((student, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${student.Flight}</td>
                                <td>${student.student_number}</td>
                                <td>${student.student_given_name} - ${student.student_family_name}</td>
                                <td>${student.host_given_name} - ${student.host_family_name}</td>
                                <td>${student.Address} , ${student.City}</td>
                                <td>${student.client}</td>
                                <td>${student.waiting_for_student_at_airport || ''}</td>
                                <td>${student.student_in_car_to_host || ''}</td>
                                <td>${student.student_delivered_to_homestay_home || ''}</td>
                            </tr>
                        `).join('');

                        let printContent = `
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Student Data Report</title>
                                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
                                <style>
                                    body { font-family: Arial, sans-serif; }
                                    .container { max-width: 100%; margin: 20px; }
                                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                    table, th, td { border: 1px solid black; }
                                    th, td { padding: 8px; text-align: left; font-size: 12px; }
                                    @media print {
                                        .no-print { display: none; }
                                        body { margin: 0; padding: 15px; }
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <h1 class="text-center fw-bold">Transport Report</h1>

                                    <h3 class="mt-5 mb-4">Driver: ${selectedSubDriverUsername} - ${data.students[0].Date}</h3>

                                    <table>
                                        <thead>${tableHeaders}</thead>
                                        <tbody>${tableRows}</tbody>
                                    </table>
                                    <p class="mt-3">Total Records: ${data.students.length}</p>
                                </div>
                            </body>
                            </html>`;

                        printWindow.document.write(printContent);
                        printWindow.document.close();
                        printWindow.print();
                        
                        printWindow.onafterprint = function() {
                            printWindow.close();
                        };
                    } else {
                        notyf.error(data.message || 'Failed to fetch student data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notyf.error('An error occurred while fetching data');
                });
            });
        });
    </script>
				