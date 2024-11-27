<?php
session_start();
include 'connection.php';

// Check if 'student_id' exists in the session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];

    // Retrieve current user's data from the database
    $query = "SELECT student_name, student_email, student_semester FROM student WHERE student_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data if exists
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        // Redirect to the dashboard if no data found
        header("Location: dashboardpage.php");
        exit();
    }

    $stmt->close();
} else {
    // Redirect to login page if student_id is not set
    header("Location: signinpage.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if required POST fields are set
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

    // Validate name and email
    if (empty($name) || empty($email)) {
        echo "<script>
                alert('Name and email are required.');
                window.location.href = 'dashboardpage.php';
              </script>";
        exit();
    }

    // Determine the query and bind parameters based on whether password is provided
    if (!empty($password)) {
        // Update with plain text password
        $query = "UPDATE student SET student_name = ?, student_semester = ?, student_email = ?, student_password = ? WHERE student_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("ssssi", $name, $semester, $email, $password, $student_id);
    } else {
        // Update without password
        $query = "UPDATE student SET student_name = ?, student_semester = ?, student_email = ? WHERE student_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("sssi", $name, $semester, $email, $student_id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>
                alert('Profile updated successfully!');
                window.location.href = 'dashboardpage.php';
              </script>";
        // Update session variable
        $_SESSION['student_name'] = $name;
        exit();
    } else {
        echo "<script>
                alert('Error updating profile: " . $stmt->error . "');
                window.location.href = 'dashboardpage.php';
              </script>";
    }

    // Close statement and connection
    $stmt->close();
    $connect->close();
}
?>
