<?php
include 'connection.php'; 

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if date is provided
if (isset($data['date'])) {
    $date = $data['date'];

    // Prepare the SQL statement to delete the event by date
    $query = "DELETE FROM events WHERE event_date = ?";
    if ($stmt = $connect->prepare($query)) {
        // Bind the date parameter
        $stmt->bind_param("s", $date);

        // Execute the statement
        if ($stmt->execute()) {
            // Send a success response
            echo json_encode(["success" => true, "message" => "Event deleted successfully."]);
        } else {
            // Send a failure response if deletion failed
            echo json_encode(["success" => false, "message" => "Failed to delete event."]);
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Send an error response if the SQL statement couldn't be prepared
        echo json_encode(["success" => false, "message" => "Failed to prepare SQL statement."]);
    }

} else {
    // Send an error response if the date is not provided
    echo json_encode(["success" => false, "message" => "Event date is required."]);
}

// Close the database connection
$connect->close();
?>
