<?php
include 'connection.php';
$data = array();

// Extracting form data (assuming it's a POST request)
$id = $_POST['id'];
$name = $_POST['name'];
$msg = $_POST['msg'];

// Query to insert the new discussion (or reply)
$sql = "INSERT INTO `discussion` (parent_comment, student, post, date) VALUES ('$id', '$name', '$msg', NOW())";
if (mysqli_query($connect, $sql)) {
    // On successful insertion
    $data['statusCode'] = 200;
} else {
    // In case of query failure
    $data['statusCode'] = 201;
}

echo json_encode($data);
mysqli_close($connect); // Close the connection
exit();
?>
