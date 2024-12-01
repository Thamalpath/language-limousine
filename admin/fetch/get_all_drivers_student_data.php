<?php
session_start();
include '../config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : null;

if (!$selectedDate) {
    echo json_encode(['success' => false, 'message' => 'Date is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT 
        s.*, d.username as driver_name
        FROM students s
        LEFT JOIN drivers d ON s.driverId = d.driverId
        WHERE s.Date = :selectedDate
        ORDER BY d.username, s.actual_arrival_time");

    $stmt->execute([
        'selectedDate' => $selectedDate
    ]);

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'students' => $students
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>