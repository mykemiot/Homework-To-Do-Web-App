<?php
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = intval($_POST['project_id']);
    $project_name = $_POST['project_name'];
    $project_desc = $_POST['project_desc'];
    $project_duedate = $_POST['project_duedate'];
    $project_status = $_POST['project_status'];

    // Update query to include project_status
    $query = "UPDATE project SET project_name = ?, project_desc = ?, project_duedate = ?, project_status = ? WHERE project_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ssssi", $project_name, $project_desc, $project_duedate, $project_status, $project_id);

    if ($stmt->execute()) {
        // Handle file upload if a new file is provided
        if (!empty($_FILES['new_file']['name'])) {
            $file_name = $_FILES['new_file']['name'];
            $file_path = 'uploads/' . $file_name;
            move_uploaded_file($_FILES['new_file']['tmp_name'], $file_path);

            // Update file information
            $file_query = "INSERT INTO file (project_id, file_name, file_path) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE file_name = VALUES(file_name), file_path = VALUES(file_path)";
            $file_stmt = $connect->prepare($file_query);
            $file_stmt->bind_param("iss", $project_id, $file_name, $file_path);
            $file_stmt->execute();
            $file_stmt->close();
        }
        
        echo "<script>
                alert('Project updated successfully.');
                window.location.href = 'dashboardpage.php';
            </script>";
    } else {
        echo "<script>
                alert('Error updating project.');
            </script>";
    }

    $stmt->close();
    $connect->close();
}
?>
