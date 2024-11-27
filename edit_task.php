<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
    $task_name = trim($_POST['task_name']);
    $task_desc = trim($_POST['task_desc']);
    $task_due_date = $_POST['task_due_date'];
    $task_status = $_POST['task_status'];
    $project_id = $_POST['project_id'];

    $query = "UPDATE task SET project_id = ?, task_name = ?, task_desc = ?, task_due_date = ?, task_status = ? WHERE task_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("issssi", $project_id, $task_name, $task_desc, $task_due_date, $task_status, $_POST['task_id']);
    $stmt->execute();

    header("Location: dashboardpage.php");
}
?>
