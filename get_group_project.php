<?php
include('connection.php'); 

// Check if the group_id parameter is provided
if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];

    // Prepare the SQL query to fetch the project details
    $query = "SELECT group_id, project_name, project_desc, due_date, members_needed FROM group_project WHERE group_id = ?";
    
    // Prepare the statement
    if ($stmt = $connect->prepare($query)) {
        $stmt->bind_param("i", $group_id); // Bind the group_id parameter
        $stmt->execute(); // Execute the query
        $result = $stmt->get_result(); // Get the result
        
        // Check if we have a matching row
        if ($result->num_rows > 0) {
            // Fetch the project data
            $project = $result->fetch_assoc();
            echo json_encode($project); // Return the project details as a JSON response
        } else {
            // If no project is found, return an error message
            echo json_encode(['error' => 'Project not found']);
        }
        
        $stmt->close(); // Close the prepared statement
    } else {
        // Return an error if the query couldn't be prepared
        echo json_encode(['error' => 'Failed to prepare query']);
    }
} else {
    // Return an error if the group_id is not provided
    echo json_encode(['error' => 'group_id not provided']);
}
?>
