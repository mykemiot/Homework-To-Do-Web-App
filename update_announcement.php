<?php
include 'connection.php'; // Ensure this includes your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'];
    $announcement = $_POST['announcement'];

    $query = "UPDATE group_project SET latest_announcement = ? WHERE group_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("si", $announcement, $groupId);

    if ($stmt->execute()) {
        header("Location: dashboardpage.php"); // Redirect to the appropriate page
        exit();
    } else {
        echo "Error updating announcement.";
    }
}
?>
