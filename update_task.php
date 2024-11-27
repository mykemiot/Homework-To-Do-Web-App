<?php
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $task_name = $_POST['task_name'];
    $task_desc = $_POST['task_desc'];
    $task_duedate = $_POST['task_duedate'];
    $task_status = $_POST['task_status'];
    $project_id = $_POST['project_id'];

    // Update query to include project_status and assigned_to
    $query = "UPDATE task SET task_name = ?, task_desc = ?, task_duedate = ?, task_status = ?, project_id = ? WHERE task_id = ?";
    $stmt = $connect->prepare($query);

    // Ensure all variables are defined
    $stmt->bind_param("ssssii", $task_name, $task_desc, $task_duedate, $task_status, $project_id, $task_id);


    if ($stmt->execute()) {
        echo "<script>
            alert('Task updated successfully.');
            window.location.href = 'dashboardpage.php';
        </script>";
    } else {
        echo "<script>
            alert('Error updating task.');
        </script>";
    }
    

    $stmt->close();
    $connect->close();
}
?>
