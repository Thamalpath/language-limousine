<?php
session_start();
include '../config/dbcon.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE ID = ?");
        $stmt->execute([$_GET['id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            // Return student data as JSON
            header('Content-Type: application/json');
            echo json_encode($student);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No ID provided']);
}