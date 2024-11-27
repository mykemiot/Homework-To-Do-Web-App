<?php
// Include the database connection file
include('connection.php');

// Check if the form is submitted and group_id is present
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_id'])) {
    // Retrieve form data
    $group_id = $_POST['group_id'];
    $project_name = $_POST['project_name'];
    $project_desc = $_POST['project_desc'];
    $due_date = $_POST['due_date'];
    $members_needed = $_POST['members_needed'];

    // Prepare the SQL query to update the project
    $query = "UPDATE group_project 
              SET project_name = ?, project_desc = ?, due_date = ?, members_needed = ? 
              WHERE group_id = ?";
    
    // Prepare the statement
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ssssi", $project_name, $project_desc, $due_date, $members_needed, $group_id); // Bind parameters
    
    // Execute the query
    if ($stmt->execute()) {
        echo "<script>
                alert('Project updated successfully!');
                window.location.href = 'dashboardpage.php'; // Redirect to another page after success
              </script>";
    } else {
        echo "Error updating project: " . $stmt->error;
    }    
    
    // Close the statement
    $stmt->close();
}
?>
