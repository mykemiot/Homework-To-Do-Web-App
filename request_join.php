<?php
session_start();
include 'connection.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

// Get the data from the request
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['group_id'])) {
    $group_id = intval($input['group_id']);
    $student_id = $_SESSION['student_id'];

    // Insert the join request into group_join_requests table
    $sql = "INSERT INTO group_join_requests (group_id, student_id, request_status, requested_at) 
            VALUES (?, ?, 'pending', NOW())";
    
    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("ii", $group_id, $student_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Join request sent successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error sending join request."]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Error preparing SQL statement."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Group ID not provided."]);
}

$connect->close();
?>
