<?php
session_start();
include '../config/dbcon.php';

if (!isset($_SESSION['signed_in']) || !$_SESSION['signed_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$driverId = isset($_POST['driverId']) ? $_POST['driverId'] : null;
$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : null;

if (!$driverId || !$selectedDate) {
    echo json_encode(['success' => false, 'message' => 'Driver ID and Date are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT 
        ID, Date, Trip, actual_arrival_time, arr_time_dep_pu_time, 
        Flight, DI, M_or_F, student_number, student_given_name, 
        student_family_name, host_given_name, host_family_name, 
        Phone, Address, City, Special_instructions, Study_Permit, 
        School, staff_member_assigned, client, waiting_for_student_at_airport, 
        student_in_car_to_host, student_delivered_to_homestay_home 
        FROM students 
        WHERE driverId = :driverId 
        AND Date = :selectedDate
        ORDER BY actual_arrival_time");

    $stmt->execute([
        'driverId' => $driverId,
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
