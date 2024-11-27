<?php
session_start();
include("connection.php");

// Check if the user is logged in (student)
if (!isset($_SESSION['student_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: signinpage.php');
    exit;
}

// Get the search query from the POST request
$query = isset($_POST['query']) ? $_POST['query'] : '';

// SQL query to search the database for projects and tasks
$sql = "
    SELECT 'project' AS type, project_name AS name, project_desc AS description
    FROM project
    WHERE project_name LIKE ? OR project_desc LIKE ?
    UNION
    SELECT 'task' AS type, task_name AS name, task_desc AS description
    FROM task
    WHERE task_name LIKE ? OR task_desc LIKE ?
";
$stmt = $connect->prepare($sql);
$searchTerm = '%' . $query . '%'; // Wildcard for partial matching
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Create an array to store the results
$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = [
        'type' => $row['type'],        // Either 'project' or 'task'
        'name' => $row['name'],        // Name of the project or task
        'description' => $row['description'] // Description of the project or task
    ];
}

// Return the results as JSON
echo json_encode($results);

// Close the statement and connection
$stmt->close();
$connect->close();
?>
