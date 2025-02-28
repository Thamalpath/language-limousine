<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
$message = ob_get_clean();

include 'config/dbcon.php';

$searchValue = $_POST['searchValue'] ?? ''; // Get the search value from the form

// Initialize variables for tracking information
$trackingInfo = [
    'waiting' => '',
    'inCar' => '',
    'delivered' => ''
];
$statusClass = ['waiting' => '', 'inCar' => '', 'delivered' => '', 'successful' => ''];
$student = null;

if ($searchValue) {
    // Prepare the SQL query to fetch student data based on student number or name
    $sql = "SELECT * FROM students 
            WHERE student_number = :studentNumber 
            OR student_given_name LIKE :givenName 
            OR student_family_name LIKE :familyName";
            
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters separately with unique names
    $stmt->execute([
        ':studentNumber' => $searchValue,
        ':givenName' => "%$searchValue%",
        ':familyName' => "%$searchValue%"
    ]);

    $student = $stmt->fetch();
}

if ($student) {
    // Fetch tracking information based on time presence in the columns
    $trackingInfo['waiting'] = $student['waiting_for_student_at_airport'] ?? '';
    $trackingInfo['inCar'] = $student['student_in_car_to_host'] ?? '';
    $trackingInfo['delivered'] = $student['student_delivered_to_homestay_home'] ?? '';
    
    // Set status classes
    $statusClass['waiting'] = !empty($trackingInfo['waiting']) ? 'active' : '';
    $statusClass['inCar'] = !empty($trackingInfo['inCar']) ? 'active' : '';
    $statusClass['delivered'] = !empty($trackingInfo['delivered']) ? 'active' : '';
    $statusClass['successful'] = $statusClass['delivered'];
}
?>

<?php include 'partials/header1.php';?>

        <!-- header-start -->
        <?php include 'partials/navbar2.php';?>
        <!-- header-start-end -->

        <!-- main-area -->
        <main>

            <!-- breadcrumb-area -->
            <div class="breadcrumb-area breadcrumb-bg s-breadcrumb-bg">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="breadcrumb-content">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="dots"></li>
                                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Tracking</li>
                                        <li class="dots2"></li>
                                    </ol>
                                </nav>
                                <h2>Tracking Here</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- breadcrumb-area-end -->

            <!-- tracking-area -->
            <div class="tracking-area pt-95 pb-115">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10">
                            <div class="tracking-id-info text-center">
                                <h4>Enter Student Number or Name</h4>
                                <form action="" method="POST" class="tracking-id-form">
                                    <input type="text" name="searchValue" placeholder="Student Number or Name" value="<?= htmlspecialchars($searchValue) ?>">
                                    <button type="submit" class="btn red-btn">Submit</button>
                                </form>
                                <div class="tracking-list">
                                    <ul>
                                        <li class="<?= $statusClass['waiting'] ?>">
                                            <div class="tracking-list-icon"><i class="flaticon-air-freight"></i></div>
                                            <div class="tracking-list-content">
                                                <p>Waiting</p>
                                                <?php if ($trackingInfo['waiting']) : ?>
                                                    <small><?= htmlspecialchars($trackingInfo['waiting']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                        </li>
                                        <li class="<?= $statusClass['inCar'] ?>">
                                            <div class="tracking-list-icon"><i class="flaticon-fast-delivery"></i></div>
                                            <div class="tracking-list-content">
                                                <p>Transit</p>
                                                <?php if ($trackingInfo['inCar']) : ?>
                                                    <small><?= htmlspecialchars($trackingInfo['inCar']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                        <li class="<?= $statusClass['delivered'] ?>">
                                            <div class="tracking-list-icon"><i class="flaticon-placeholder"></i></div>
                                            <div class="tracking-list-content">
                                                <p>Delivered</p>
                                                <?php if ($trackingInfo['delivered']) : ?>
                                                    <small><?= htmlspecialchars($trackingInfo['delivered']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                        <li class="<?= $statusClass['successful'] ?>">
                                            <div class="tracking-list-icon"><i class="flaticon-audit"></i></div>
                                            <div class="tracking-list-content">
                                                <p>Successful</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- tracking-area-end -->

        </main>
        <!-- main-area-end -->

        <!-- footer -->
        <?php include 'partials/footer.php';?>        
        <!-- footer-end -->
