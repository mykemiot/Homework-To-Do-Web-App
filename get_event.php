<?php
include("connection.php");

header('Content-Type: application/json');

// Start session to get student_id
session_start();
$student_id = $_SESSION['student_id'] ?? null; // Replace with your session management

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$query = "SELECT event_id, event_title AS title, event_desc AS description, event_date AS date 
          FROM events 
          WHERE student_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'events' => $events,
]);
