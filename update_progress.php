<?php
// Connect to the database
include("connection.php");

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['group_id']) && isset($input['progress'])) {
    $group_id = intval($input['group_id']);
    $progress = intval($input['progress']);

    // Validate progress range (0-100)
    if ($progress < 0 || $progress > 100) {
        echo json_encode(['success' => false, 'message' => 'Invalid progress value.']);
        exit;
    }

    // Update progress in the database
    $query = "UPDATE group_project SET progress = ? WHERE group_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ii", $progress, $group_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Progress updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update progress.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}

$connect->close();
?>
