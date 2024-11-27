<?php
include("connection.php");

$group_id = $_GET['group_id'];

// Fetch group links
$sqlFetchLinks = "SELECT whatsapp_link, telegram_link FROM group_links WHERE group_id = ?";
$stmt = $connect->prepare($sqlFetchLinks);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();
$links = $result->fetch_assoc();

echo json_encode($links);

$stmt->close();
$connect->close();
?>
