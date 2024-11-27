<?php
session_start();
include("connection.php");

// Check if the user is logged in (lecture)
if (!isset($_SESSION['id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: signinpagelecture.php');
    exit;
}

// Get the search query from the POST request
$query = isset($_POST['query']) ? $_POST['query'] : '';

// SQL query to search the database
$sql = "SELECT * FROM project WHERE project_name LIKE ? OR project_desc LIKE ?";
$stmt = $connect->prepare($sql);
$searchTerm = '%' . $query . '%';  // Wildcard for partial matching
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Create an array to store the results
$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = [
        'name' => $row['project_name'],
        'description' => $row['project_desc']
    ];
}

// Return the results as JSON
echo json_encode($results);

// Close the statement and connection
$stmt->close();
$connect->close();
?>
