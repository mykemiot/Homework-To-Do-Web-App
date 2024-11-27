<?php
// Start session
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the landing page
header("Location: landingpage.php");
exit();
?>
