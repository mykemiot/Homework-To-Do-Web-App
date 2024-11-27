<?php
include 'connection.php';
$data = array();

// Query to fetch all discussions
$sql = "SELECT * FROM `discussion` ORDER BY id DESC";
$result = mysqli_query($connect, $sql);

// Check if the query was successful
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($data, $row);
    }
} else {
    // In case of query failure
    echo json_encode(array('error' => 'Query failed.'));
}

echo json_encode($data);
mysqli_close($connect); // Close the connection
exit();
?>
