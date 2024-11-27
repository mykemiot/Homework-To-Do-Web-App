<?php
session_start(); // Start the session
include("connection.php");

// Ensure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

// Get form data
$task_name = $_POST['task_name'];
$task_desc = $_POST['task_desc'];
$task_duedate = $_POST['task_duedate'];
$task_status = $_POST['task_status'];
$project_id = $_POST['project_id'];

// Insert the new task into the database
$sql = "INSERT INTO task (project_id, task_name, task_desc, task_duedate, task_status)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $connect->prepare($sql);
$stmt->bind_param("issss", $project_id, $task_name, $task_desc, $task_duedate, $task_status);

if ($stmt->execute()) {
    echo "<script>
                alert('Task added successfully!');
                window.location.href = 'dashboardpage.php';
          </script>";
} else {
    echo "<script>
                alert('Error adding task: " . $stmt->error . "');
                window.location.href = 'dashboardpage.php';
          </script>";
}

$stmt->close();
$connect->close();
?>
