<?php
session_start();
include('connection.php');

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Check if task_id is passed via GET request
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Prepare SQL query to delete the task
    $sql = "DELETE FROM task WHERE task_id = ?";

    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("i", $task_id);
        $stmt->execute();

        // Check if the task was successfully deleted
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found or could not be deleted.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Task ID not provided.']);
}

$connect->close();
?>
