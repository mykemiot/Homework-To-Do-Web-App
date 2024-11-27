<?php
session_start();
include('connection.php');

// Check if task_id is passed via GET request
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Prepare SQL query to fetch task details
    $sql = "
        SELECT t.task_id, t.task_name, t.task_desc, t.task_duedate, t.task_status,
               t.project_id,
               p.project_name
        FROM task AS t
        LEFT JOIN project AS p ON t.project_id = p.project_id
        WHERE t.task_id = ?
    ";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();

        // Return the task details as a JSON response
        echo json_encode([
            'success' => true,
            'task' => [
                'task_id' => $task['task_id'],
                'task_name' => $task['task_name'],
                'task_desc' => $task['task_desc'],
                'task_duedate' => $task['task_duedate'],
                'task_status' => $task['task_status'],
                'project_id' => $task['project_id'],
                'project_name' => $task['project_name']
            ]
        ]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}

$connect->close();
?>
