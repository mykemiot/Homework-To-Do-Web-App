<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
    $project_id = $_POST['project_id'];
    $project_name = trim($_POST['project_name']);
    $project_desc = trim($_POST['project_desc']);
    $project_duedate = $_POST['project_duedate'];
    $project_status = $_POST['project_status'];

    $query = "UPDATE project SET project_name = ?, project_desc = ?, project_duedate = ?, project_status = ? WHERE project_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ssssi", $project_name, $project_desc, $project_duedate, $project_status, $project_id);
    $stmt->execute();

    // Check for file upload
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == 0) {
        $file_name = $_FILES['project_file']['name'];
        $file_tmp = $_FILES['project_file']['tmp_name'];
        $file_path = "uploads/" . basename($file_name);
        move_uploaded_file($file_tmp, $file_path);

        // Update file details
        $file_query = "UPDATE file SET file_name = ?, file_path = ? WHERE project_id = ?";
        $file_stmt = $connect->prepare($file_query);
        $file_stmt->bind_param("ssi", $file_name, $file_path, $project_id);
        $file_stmt->execute();
    }

    header("Location: dashboardpage.php");
}
?>
