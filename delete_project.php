<?php
// delete_project.php
session_start();
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

// Check if project_id is set
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    // Prepare and execute delete query
    $stmt = $connect->prepare("DELETE FROM project WHERE project_id = ?");
    
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Prepare statement failed: " . $connect->error]);
        exit();
    }

    $stmt->bind_param("i", $project_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Execution failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "No project ID provided."]);
}

$connect->close();
?>
