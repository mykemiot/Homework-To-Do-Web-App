<?php
// Start session and include the database connection
include 'connection.php';

// Get the date for 1 day from now
$notificationDate = date("Y-m-d", strtotime("+1 day"));

// Select projects that are due the next day and don't have a due notification sent yet
$query = "
    SELECT project_id, project_name, student_id, project_duedate 
    FROM project 
    WHERE project_duedate = ? AND project_id NOT IN (
        SELECT project_id FROM notification WHERE notification_text = 'Project due tomorrow'
    )";

$stmt = $connect->prepare($query);
$stmt->bind_param("s", $notificationDate);
$stmt->execute();
$result = $stmt->get_result();

while ($project = $result->fetch_assoc()) {
    $student_id = $project['student_id'];
    $project_id = $project['project_id'];
    $project_name = $project['project_name'];

    // Insert a new notification for the student
    $notificationText = "Reminder: Project '$project_name' is due tomorrow!";
    $insertQuery = "INSERT INTO notification (student_id, project_id, notification_text, notification_date, is_read) VALUES (?, ?, ?, NOW(), 0)";
    $insertStmt = $connect->prepare($insertQuery);
    $insertStmt->bind_param("iis", $student_id, $project_id, $notificationText);
    $insertStmt->execute();
    $insertStmt->close();
}

// Close database connections
$stmt->close();
$connect->close();
?>
