<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ob_start(); 
session_start();
$message = ob_get_clean();
include 'config/dbcon.php';
date_default_timezone_set('America/Vancouver');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Form Validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Error: All fields are required.";
        header("Location: ./");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Error: Invalid email format.";
        header("Location: ./");
        exit();
    }

    // Fetch user data from database
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="icon" href="assets/images/logo-icon.png" type="image/png" />
    <link rel="stylesheet" href="assets/css/LoginStyle.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>
<body>
    <div class="wrapper">
        <form action="index.php" method="POST">
            <h1>Sign In</h1>
            <hr style="margin-top: 20px;">
            <div class="input-box">
                <input type="text" name="email" placeholder="Email">
                <i class='bx bxs-envelope'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password">
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <!-- Add this script to initialize Notyf.js -->
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
</body>
</html>
