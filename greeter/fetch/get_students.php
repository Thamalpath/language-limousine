<?php
include '../config/dbcon.php';

if(isset($_POST['date'])) {
    $date = $_POST['date'];
    
    $query = "SELECT student_number, student_given_name 
            FROM students 
            WHERE Date = :date";
            
    $stmt = $pdo->prepare($query);
    $stmt->execute(['date' => $date]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($students);
}
