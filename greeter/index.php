<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start(); 
session_start();
$message = ob_get_clean();
include 'config/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Form Validation
  if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Error: All fields are required.";
    header("Location: ./");
    exit();
  }

  // Fetch user data from database
  $sql = "SELECT * FROM greeters WHERE email = :email";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['email' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['gre_id'];
      $_SESSION['email'] = $user['email'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['success'] = "Success: Sign in successful!";
      $_SESSION['signed_in'] = true;
  } else {
      $_SESSION['error'] = "Error: Invalid email or password.";
      header("Location: ./");
      exit();
  }
  header("Location: ./");
  exit();
}

$message = ob_get_clean();
?>

<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Language Limousine | Sign In</title>
  <!--favicon-->
	<link rel="icon" href="assets/images/logo-icon.png" type="image/png">
  <!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet">
	<script src="assets/js/pace.min.js"></script>

  <!--plugins-->
  <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/mm-vertical.css">
  <!--bootstrap css-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

  </head>

  <body>

    <!--authentication-->
    <div class="auth-basic-wrapper d-flex align-items-center justify-content-center">
      <div class="container-fluid my-5 my-lg-0">
        <div class="row">
           <div class="col-12 col-md-8 col-lg-6 col-xl-5 col-xxl-4 mx-auto">
            <div class="card rounded-4 mb-0 border-top border-4 border-primary border-gradient-1">
              <div class="card-body p-5">
                  <img src="assets/images/logo-icon.png" class="mb-4" width="145" alt="">
                  <h2 class="fw-bold">Language Limousine</h2>
                  <h4 class="mb-0">Sign In</h4>

                  <div class="form-body my-5">
                    <form class="row g-3" action="./" method="POST">
											<div class="col-12">
												<label for="email" class="form-label">Email</label>
												<input type="text" class="form-control" id="email" name="email" placeholder="Enter Email">
											</div>
											<div class="col-12 mb-4">
												<label for="password" class="form-label">Password</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" class="form-control border-end-0" id="password" name="password" placeholder="Enter Password"> 
                          <a href="javascript:;" class="input-group-text bg-transparent"><i class="bi bi-eye-slash-fill"></i></a>
												</div>
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-grd-primary fw-bold text-white">Login</button>
												</div>
											</div>
										</form>
									</div>
                </div>
              </div>
            </div>
           </div>
        </div><!--end row-->
     </div>
    </div>
    <!--authentication-->


    <!--plugins-->
    <script src="assets/js/jquery.min.js"></script>
    <!-- Initialize Notyf.js -->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        // Initialize Notyf
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top',
            },
        });

        <?php
        // Display success or error alerts
        if (isset($_SESSION['success'])) {
            echo "notyf.success('{$_SESSION['success']}');";
            unset($_SESSION['success']);
            echo "setTimeout(function(){ window.location.href = 'dashboard'; }, 3000);";
        }

        if (isset($_SESSION['error'])) {
            echo "notyf.error('{$_SESSION['error']}');";
            unset($_SESSION['error']);
        }
        ?>
    </script>

    <script>
      $(document).ready(function () {
        $("#show_hide_password a").on('click', function (event) {
          event.preventDefault();
          if ($('#show_hide_password input').attr("type") == "text") {
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass("bi-eye-slash-fill");
            $('#show_hide_password i').removeClass("bi-eye-fill");
          } else if ($('#show_hide_password input').attr("type") == "password") {
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass("bi-eye-slash-fill");
            $('#show_hide_password i').addClass("bi-eye-fill");
          }
        });
      });
    </script>
  
  </body>
</html>