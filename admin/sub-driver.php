<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();
date_default_timezone_set('America/Vancouver');

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || !in_array($_SESSION['role'], ['Admin'])) {
    header("Location: ./");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subdri_id = isset($_POST['subdri_id']) ? trim($_POST['subdri_id']) : null;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $driverId = trim($_POST['driverId']);
    $vehicle_no = trim($_POST['vehicle_no']);
    $status = 'offduty'; // Default status for new sub-drivers
    $role = 'SubDriver';

    try {
        if ($subdri_id) {
            // Update existing driver
            $sql = "UPDATE `sub-drivers` SET username = :username, email = :email,
                    driverId = :driverId, vehicle_no = :vehicle_no, status = :status";
            $params = [
                'username' => $username,
                'email' => $email,
                'driverId' => $driverId,
                'vehicle_no' => $vehicle_no,
                'status' => $_POST['status'],
                'subdri_id' => $subdri_id
            ];
            
            if (!empty($password)) {
                $sql .= ", password = :password";
                $params['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $sql .= " WHERE subdri_id = :subdri_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success'] = "Sub Driver updated successfully.";
        } else {
            if (empty($password)) {
                $_SESSION['error'] = "Error: Password is required.";
                $_SESSION['form_data'] = $_POST;
                header("Location: sub-driver");
                exit();
            }
            
            // Check if the email already exists in the database
            $sql_check_email = "SELECT COUNT(*) AS count FROM `sub-drivers` WHERE email = :email";
            $stmt_check_email = $pdo->prepare($sql_check_email);
            $stmt_check_email->execute(['email' => $email]);
            $result_check_email = $stmt_check_email->fetch(PDO::FETCH_ASSOC);

            if ($result_check_email['count'] > 0) {
                $_SESSION['error'] = "Error: Email already exists. Please choose a different email.";
                $_SESSION['form_data'] = $_POST;
                header("Location: sub-driver");
                exit();
            }
        
            if (strlen($password) < 8) {
                $_SESSION['error'] = "Error: Password must be at least 8 characters.";
                $_SESSION['form_data'] = $_POST;
                header("Location: sub-driver");
                exit();
            }

            // Insert new driver
            $sql = "INSERT INTO `sub-drivers` (username, email, password, driverId, vehicle_no, status, role) 
                    VALUES (:username, :email, :password, :driverId, :vehicle_no, :status, :role)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'driverId' => $driverId,
                'vehicle_no' => $vehicle_no,
                'status' => $status,
                'role' => $role
            ]);
            $_SESSION['success'] = "New sub driver added successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: sub-driver");
    exit();
}

$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);

// Fetch driver data if ID is provided
if (isset($_GET['id'])) {
    $subdri_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM `sub-drivers` WHERE subdri_id = :subdri_id");
    $stmt->execute(['subdri_id' => $subdri_id]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($driver);
    exit();
}

// Check if delete request is submitted
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare("DELETE FROM `sub-drivers` WHERE subdri_id = ?");
        // Bind parameters and execute the statement
        $stmt->execute([$delete_id]);
        // Set success message
        $_SESSION['success'] = "Success: Data deleted successfully";
    } catch (PDOException $e) {
        // Set error message
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect back to the referring page
    header("Location: sub-driver");
    exit();
}

// Fetch all sub-drivers
$query = "SELECT `subdri_id`, `username`, `email`, `password`, `driverId`, `vehicle_no`, `status`, `role` FROM `sub-drivers`";
$stmt = $pdo->prepare($query);
$stmt->execute();
$sub_drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                                                    <form id="subDriverForm" class="row g-3 needs-validation" method="POST" novalidate>
                                                        <div class="card-title d-flex align-items-center">
                                                            <h5 id="heading" class="mb-0 text-primary font-24 fw-bold">Add Sub Driver User</h5>
                                                        </div>
                                                        <hr>
                                                        <input type="hidden" name="subdri_id" id="subdri_id" value="<?php echo isset($form_data['subdri_id']) ? htmlspecialchars($form_data['subdri_id']) : ''; ?>">
                                                        <div class="col-md-4">
                                                            <label for="username" class="form-label fw-bold font-18">Username</label>
                                                            <input type="text" class="form-control" name="username" id="username" value="<?php echo isset($form_data['username']) ? htmlspecialchars($form_data['username']) : ''; ?>" required>
                                                            <div class="invalid-feedback">Please provide a username.</div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="email" class="form-label fw-bold font-18">Email</label>
                                                            <input type="email" class="form-control" name="email" id="email" value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>" required>
                                                            <div class="invalid-feedback">Please provide a valid email.</div>
                                                        </div>
                                                        <div class="col-md-4 mb-4">
                                                            <label for="password" class="form-label fw-bold font-18">Password</label>
                                                            <input type="password" class="form-control" name="password" id="password" value="<?php echo isset($form_data['password']) ? htmlspecialchars($form_data['password']) : ''; ?>">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label for="driverId" class="form-label fw-bold font-18">Driver ID</label>
                                                            <input type="text" class="form-control" name="driverId" id="driverId" value="<?php echo isset($form_data['driverId']) ? htmlspecialchars($form_data['driverId']) : ''; ?>" required>
                                                            <div class="invalid-feedback">Please provide a driver ID.</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="vehicle_no" class="form-label fw-bold font-18">Vehicle No</label>
                                                            <input type="text" class="form-control" name="vehicle_no" id="vehicle_no" value="<?php echo isset($form_data['vehicle_no']) ? htmlspecialchars($form_data['vehicle_no']) : ''; ?>" required>
                                                            <div class="invalid-feedback">Please provide a vehicle No.</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="status" class="form-label fw-bold font-18">Status</label>
                                                            <select class="form-select" name="status" id="status" required>
                                                                <option value="" selected disabled>Select Status</option>
                                                                <option value="onduty" <?php echo (isset($form_data['status']) && $form_data['status'] == 'onduty') ? 'selected' : ''; ?>>On Duty</option>
                                                                <option value="offduty" <?php echo (isset($form_data['status']) && $form_data['status'] == 'offduty') ? 'selected' : ''; ?>>Off Duty</option>
                                                            </select>
                                                            <div class="invalid-feedback">Please select a status.</div>
                                                        </div> 
                                                        <div class="col-md-3 mb-4">
                                                            <label for="role" class="form-label fw-bold font-18">Role</label>
                                                            <input type="text" class="form-control" name="role" id="role" value="SubDriver" readonly>
                                                        </div>
                                                        <div class="col-md-12 text-center g-3 mt-5">
                                                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                                <button type="submit" id="submitBtn" class="btn btn-gradient-info fw-bold px-5">Submit</button>
                                                                <button type="reset" class="btn btn-gradient-primary fw-bold px-5">Reset</button>
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

				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Driver ID</th>
                                        <th>Vehicle No</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($sub_drivers)): ?>
                                        <?php foreach ($sub_drivers as $index => $user): ?>
                                            <tr data-id="<?php echo $user['subdri_id']; ?>">
                                                <td><?php echo htmlspecialchars($index + 1); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['driverId']); ?></td>
                                                <td><?php echo htmlspecialchars($user['vehicle_no']); ?></td>
                                                <td><?php echo htmlspecialchars($user['status']); ?></td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                        <a href="#" data-id="<?php echo $user['subdri_id']; ?>" class="btn btn-gradient-danger px-4 delete-btn">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">No Sub Driver users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
            const form = document.getElementById('subDriverForm');
            const submitBtn = document.getElementById('submitBtn');
            const heading = document.getElementById('heading');
            const table = document.getElementById('example');

            // Handle form submission
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Handle table row click
            table.addEventListener('click', function(e) {
                if (e.target.tagName === 'TD') {
                    const row = e.target.closest('tr');
                    const id = row.dataset.id;
                    fetchDriverData(id);
                }
            });

            function fetchDriverData(id) {
                fetch(`sub-driver.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('subdri_id').value = data.subdri_id;
                        document.getElementById('username').value = data.username;
                        document.getElementById('email').value = data.email;
                        document.getElementById('password').value = '';  // Clear password field for security
                        document.getElementById('driverId').value = data.driverId;
                        document.getElementById('vehicle_no').value = data.vehicle_no;
                        document.getElementById('role').value = data.role;
                        document.getElementById('status').value = data.status;

                        submitBtn.textContent = 'Update';
                        submitBtn.classList.remove('btn-gradient-info');
                        submitBtn.classList.add('btn-gradient-warning');
                        submitBtn.classList.add('fw-bold');

                        // Change heading text and color
                        heading.textContent = 'Update Driver User';
                        heading.classList.remove('text-primary');  
                        heading.classList.add('text-warning');
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>

    <script>
		document.addEventListener('DOMContentLoaded', function() {

			document.querySelectorAll('.delete-btn').forEach(function(button) {
				button.addEventListener('click', function(e) {
					e.preventDefault();
					const deleteId = this.getAttribute('data-id');
					
					// Create the custom confirmation dialog
					const confirmationDialog = document.createElement('div');
					confirmationDialog.classList.add('confirmation-dialog'); 
					confirmationDialog.innerHTML = `
						<div>
							<img src="assets/images/icons/wired-outline-1140-error.gif" alt="Warning Icon" class="warning-icon">
							<p class="fs-3 text-black fw-bold">Are you sure ?</p>
							<p class="fs-6" style="color: #6c757d;">You won't be able to revert this!</p>
							<div class="button-container">
								<button id="yesButton" class="btn btn-danger">Yes</button>
								<button id="noButton" class="btn btn-secondary">No</button>
							</div>
						</div>
					`;
					document.body.appendChild(confirmationDialog);
					
					// Handle Yes button click
					document.getElementById('yesButton').addEventListener('click', function() {
						window.location.href = 'sub-driver?delete_id=' + deleteId;
					});
					
					// Handle No button click
					document.getElementById('noButton').addEventListener('click', function() {
						document.body.removeChild(confirmationDialog);
						notyf.error('Delete canceled');
					});
				});
			});
		});
	</script>