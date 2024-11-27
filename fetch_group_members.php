<?php
$groupId = $group['group_id']; // This is passed from the main loop
$queryMembers = "
    SELECT gm.student_id, s.student_name, gm.role
    FROM group_members gm
    JOIN student s ON gm.student_id = s.student_id
    WHERE gm.group_id = ?
";
$stmtMembers = $connect->prepare($queryMembers);
$stmtMembers->bind_param("i", $groupId);
$stmtMembers->execute();
$resultMembers = $stmtMembers->get_result();

if ($resultMembers->num_rows > 0) {
    echo "<h3>Group Members</h3><ul>";
    while ($member = $resultMembers->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($member['student_name']) . " (" . htmlspecialchars($member['role']) . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No members yet.</p>";
}
$stmtMembers->close();
?>
