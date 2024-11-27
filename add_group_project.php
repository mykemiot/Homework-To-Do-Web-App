<?php
session_start();
include 'connection.php';

// Ensure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $projectName = $_POST['project_name'];
    $projectDesc = $_POST['project_desc'];
    $dueDate = $_POST['due_date'];
    $membersNeeded = $_POST['members_needed'];

    // Use the actual logged-in student ID as the leader ID
    $leaderId = $_SESSION['student_id'];

    // Insert the new group project into the group_project table
    $sql = "INSERT INTO group_project (project_name, leader_id, members_needed, project_desc, due_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $connect->error);
    }
    $stmt->bind_param("siiss", $projectName, $leaderId, $membersNeeded, $projectDesc, $dueDate);

    if ($stmt->execute()) {
        $groupId = $stmt->insert_id; // Get the ID of the newly created group project
        $stmt->close();

        // Add the leader as a member in the group_members table
        $sql_leader = "INSERT INTO group_members (group_id, student_id, role) VALUES (?, ?, 'leader')";
        $stmt_leader = $connect->prepare($sql_leader);
        if ($stmt_leader === false) {
            die("Error preparing leader statement: " . $connect->error);
        }
        $stmt_leader->bind_param("ii", $groupId, $leaderId);
        $stmt_leader->execute();
        $stmt_leader->close();

        echo "<script>
                alert('Group assignment added successfully!');
                window.location.href = 'dashboardpage.php';
              </script>";
    } else {
        echo "<script>
                alert('Error adding group assignment: " . $connect->error . "');
                window.location.href = 'dashboardpage.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid request method.');
            window.location.href = 'dashboardpage.php';
          </script>";
}

$connect->close();
?>
