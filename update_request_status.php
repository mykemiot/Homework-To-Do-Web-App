<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_SESSION['student_id'])) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit();
    }

    if (isset($input['request_id'], $input['action'])) {
        $request_id = intval($input['request_id']);
        $action = $input['action'];
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $approved_at = ($status === 'approved') ? date("Y-m-d H:i:s") : NULL;

        // Update the join request status
        $sql = "UPDATE group_join_requests SET request_status = ?, approved_at = ? WHERE request_id = ?";
        if ($stmt = $connect->prepare($sql)) {
            $stmt->bind_param("ssi", $status, $approved_at, $request_id);
            if ($stmt->execute()) {
                // If the request was approved, add the student to group_members
                if ($status === 'approved') {
                    // Fetch the group_id and student_id for this request
                    $fetchQuery = "SELECT group_id, student_id FROM group_join_requests WHERE request_id = ?";
                    $fetchStmt = $connect->prepare($fetchQuery);
                    $fetchStmt->bind_param("i", $request_id);
                    $fetchStmt->execute();
                    $result = $fetchStmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $group_id = $row['group_id'];
                        $student_id = $row['student_id'];

                        // Insert into group_members
                        $insertQuery = "INSERT INTO group_members (group_id, student_id) VALUES (?, ?)";
                        $insertStmt = $connect->prepare($insertQuery);
                        $insertStmt->bind_param("ii", $group_id, $student_id);

                        if ($insertStmt->execute()) {
                            echo json_encode(["status" => "success", "message" => "Request has been approved, and student has been added to the group."]);
                        } else {
                            echo json_encode(["status" => "error", "message" => "Error adding student to the group."]);
                        }

                        $insertStmt->close();
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error fetching request details."]);
                    }

                    $fetchStmt->close();
                } else {
                    echo json_encode(["status" => "success", "message" => "Request has been " . $status . "."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating request status."]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Error preparing SQL statement."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request data."]);
    }
}

$connect->close();
?>
