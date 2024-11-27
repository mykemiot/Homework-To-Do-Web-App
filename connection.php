<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Please replace with your database password
$database = "htdmain";

// Create connection
$connect = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
