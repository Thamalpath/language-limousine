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

$address = $city = $student_given_name = $student_family_name = $host_given_name = $host_family_name = '';
$printPreview = false;

if (isset($_POST['submit'])) {
    $student_number = $_POST['number'];

    try {
        $sql = "SELECT address, city, student_given_name, student_family_name, host_given_name, host_family_name,client
                FROM students WHERE student_number = :student_number";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $address = $row["address"];
            $city = $row["city"];
            $student_given_name = $row["student_given_name"];
            $student_family_name = $row["student_family_name"];
            $host_given_name = $row["host_given_name"];
            $host_family_name = $row["host_family_name"];
            $School = $row["client"];
        } else {
            echo "No results found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
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
                                                <div class="card-body p-4">
                                                    <form id="schoolForm" class="row g-3 needs-validation d-flex align-items-center" method="POST" novalidate>
                                                        <div class="card-title d-flex align-items-center">
                                                            <h5 id="heading" class="mb-0 text-primary font-24 fw-bold">Print Student Map</h5>
                                                        </div>
                                                        <div class="col-auto">
                                                            <label for="number" class="form-label fw-bold font-18 me-2">Student Number :</label>
                                                        </div>
                                                        <div class="col-auto">
                                                            <input type="text" class="form-control me-2" id="number" name="number" required>
                                                        </div>
                                                        <div class="col-auto">
                                                            <input type="submit" class="btn btn-gradient-info fw-bold px-5 " name="submit" value="Search">
                                                        </div>
                                                        <div class="col-auto">
                                                        <button onclick="printMap()" class="btn btn-gradient-primary fw-bold px-5">Confirm Print</button>
                                                        </div>
                                                        </form>

                                                        
                                                        <!-- <button onclick="closePreview()">Close Preview</button> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>    
                            <div class="card">
                                <div class="card-body">
                                    <?php if (!empty($address) && !empty($city)): ?>
                                    <section>
                                        <h3>Map Location</h3>
                                        <iframe 
                                            width="100%" 
                                            height="500" 
                                            src="https://maps.google.com/maps?q=<?php echo urlencode($address . ', ' . $city); ?>&output=embed">
                                        </iframe>
                                    </section>
                
                                    <div id="printPreview" style="display:none;">
                                        <div class="print-content">
                                            <h3>Student Map</h3>
                                            <div>
                                                <strong>Student Name:</strong> <?php echo $student_given_name . ' ' . $student_family_name; ?><br>
                                                <strong>Host Name:</strong> <?php echo $host_given_name . ' ' . $host_family_name; ?><br>
                                                <strong>Address:</strong> <?php echo $address; ?><br>
                                                <strong>City:</strong> <?php echo $city; ?><br>
                                                <strong>School:</strong> <?php echo $School; ?> 
                                            </div>
                                            <iframe 
                                                width="100%" 
                                                height="300" 
                                                src="https://maps.google.com/maps?q=<?php echo urlencode($address . ', ' . $city); ?>&output=embed">
                                            </iframe>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php include 'partials/endarea.php';?>
            </div>
        <?php include 'partials/footer.php';?>

<script>

function printMap() {
    var printContent = document.getElementById('printPreview').innerHTML;
    var win = window.open('', '', 'height=600,width=800');
    win.document.write('<html><head><title>Print Map</title>');
    win.document.write('<style>body{font-family: Arial, sans-serif;} .print-content {text-align: center;} </style>');
    win.document.write('</head><body>');
    win.document.write(printContent);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}
</script>