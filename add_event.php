<?php
session_start();
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_event'])) {
    // Get the form data
    $event_title = mysqli_real_escape_string($connect, $_POST['event_title']);
    $event_desc = mysqli_real_escape_string($connect, $_POST['event_desc']);
    $event_date = mysqli_real_escape_string($connect, $_POST['event_date']);
    $student_id = $_SESSION['student_id'];

    // Validate the data
    if (empty($event_title) || empty($event_date)) {
        echo "<script>alert('Event title and date are required!');</script>";
        echo "<script>window.location.href = 'dashboardpage.php';</script>";
        exit();
    }

    // Prevent duplicate entries for the same title and date
    $check_sql = "SELECT * FROM events WHERE event_title = ? AND event_date = ? AND student_id = ?";
    if ($check_stmt = $connect->prepare($check_sql)) {
        $check_stmt->bind_param("ssi", $event_title, $event_date, $student_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('You already have an event with this title and date.');</script>";
            echo "<script>window.location.href = 'dashboardpage.php';</script>";
            exit();
        }
        $check_stmt->close();
    }

    // SQL query to insert the event into the database
    $sql = "INSERT INTO events (event_title, event_desc, event_date, student_id) 
            VALUES (?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("sssi", $event_title, $event_desc, $event_date, $student_id);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>alert('Event added successfully!');</script>";
            echo "<script>window.location.href = 'dashboardpage.php';</script>";
        } else {
            echo "<script>alert('An error occurred while adding the event.');</script>";
            echo "<script>window.location.href = 'dashboardpage.php';</script>";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "<script>alert('An error occurred. Please try again later.');</script>";
        echo "<script>window.location.href = 'dashboardpage.php';</script>";
    }
}

// Close the database connection
$connect->close();
?>
