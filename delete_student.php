<?php
session_start();
include("connection.php");

// Check if the lecture is logged in
if (!isset($_SESSION['id'])) {
    header('Location: signinpagelecture.php');
    exit;
}

// Get the student ID from the URL
$student_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($student_id) {
    // Delete the student record
    $sql = "DELETE FROM student WHERE student_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $student_id);
    
    if ($stmt->execute()) {
        echo "Student deleted successfully.";
        header('Location: dashboardpagelecture.php'); // Redirect to the student list after deleting
        exit;
    } else {
        echo "Error deleting record: " . $connect->error;
    }
} else {
    echo "Invalid student ID.";
}

$stmt->close();
$connect->close();
?>
