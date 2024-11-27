<?php
session_start();
include("connection.php");

// Check if the lecture is logged in
if (!isset($_SESSION['id'])) {
    header('Location: signinpagelecture.php');
    exit;
}

// Get the student ID from the URL
$student_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($student_id) {
    // Fetch the student's current data
    $sql = "SELECT * FROM student WHERE student_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "Student not found.";
        exit;
    }
    
    // Update the student data if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $student_name = $_POST['student_name'];
        $student_email = $_POST['student_email'];
        $student_semester = $_POST['student_semester'];

        $update_sql = "UPDATE student SET student_name = ?, student_email = ?, student_semester = ? WHERE student_id = ?";
        $stmt = $connect->prepare($update_sql);
        $stmt->bind_param("sssi", $student_name, $student_email, $student_semester, $student_id);
        
        if ($stmt->execute()) {
            echo "Student information updated successfully.";
            header('Location: dashboardpagelecture.php'); // Redirect to the student list after editing
            exit;
        } else {
            echo "Error updating record: " . $connect->error;
        }
    }
} else {
    echo "Invalid student ID.";
}

$stmt->close();
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
</head>
<body>
    <h2>Edit Student</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="student_name" value="<?= htmlspecialchars($student['student_name']); ?>" required><br>

        <label>Email:</label>
        <input type="email" name="student_email" value="<?= htmlspecialchars($student['student_email']); ?>" required><br>

        <label>Semester:</label>
        <input type="text" name="student_semester" value="<?= htmlspecialchars($student['student_semester']); ?>" required><br>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
