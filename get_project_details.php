<?php
include("connection.php");

$project_id = intval($_GET['project_id']);
$query = "SELECT project_name, project_desc, project_duedate, project_status, file_name FROM project LEFT JOIN file ON project.project_id = file.project_id WHERE project.project_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}

$stmt->close();
$connect->close();
?>
