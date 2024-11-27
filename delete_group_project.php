<?php
// Start the session (if needed for authentication)
session_start();
include('connection.php'); // Database connection

// Check if the group_id is provided
if (isset($_POST['group_id'])) {
    // Sanitize and get the group_id
    $groupId = (int) $_POST['group_id']; // Ensure it's an integer

    // Prepare the SQL DELETE query
    $query = "DELETE FROM group_project WHERE group_id = ?";
    
    if ($stmt = $connect->prepare($query)) {
        // Bind the group_id to the prepared statement
        $stmt->bind_param('i', $groupId);
        
        // Execute the query and check the result
        if ($stmt->execute()) {
            // If the project is deleted successfully, return success
            echo "success";
        } else {
            // If there was an error during the execution
            echo "error";
        }
        $stmt->close(); // Close the prepared statement
    } else {
        echo "error"; // If there's an issue preparing the query
    }
} else {
    echo "No group ID provided."; // If group_id isn't sent in the POST request
}
?>
