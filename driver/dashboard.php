<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start();
session_start();
$message = ob_get_clean();

include 'config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in'] || $_SESSION['role'] !== 'Driver') {
    header("Location: ./");
    exit();
}

?>

<?php include 'partials/header.php';?>

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
      
    </div>
  </main>
  <!--end main wrapper-->

  <?php include 'partials/footer.php';?>