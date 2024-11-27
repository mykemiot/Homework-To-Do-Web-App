<?php
session_start();
include("connection.php");
echo json_encode(array("student_id" => $_SESSION['student_id'], "student_name" => $_SESSION['student_name']));
?>
