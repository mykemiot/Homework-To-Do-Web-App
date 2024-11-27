<?php
session_start();
include 'connection.php';

// Check if 'student_id' exists in the session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    // Redirect to login page if student_id is not set
    header("Location: signinpage.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the notification preferences from the form, default to false if not checked
    $task_reminders = isset($_POST['task_reminders']) ? 1 : 0;
    $project_updates = isset($_POST['project_updates']) ? 1 : 0;
    $comment_notifications = isset($_POST['comment_notifications']) ? 1 : 0;

    // Prepare the query to update the user's notification preferences
    $query = "UPDATE student SET task_reminders = ?, project_updates = ?, comment_notifications = ? WHERE student_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("iiii", $task_reminders, $project_updates, $comment_notifications, $student_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>
                alert('Notification preferences updated successfully!');
                window.location.href = 'dashboardpage.php';
              </script>";
    } else {
        echo "<script>
                alert('Error updating preferences: " . $stmt->error . "');
                window.location.href = 'dashboardpage.php';
              </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $connect->close();
}
?>
