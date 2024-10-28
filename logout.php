<?php
session_start();
include('includes/db.php'); // Make sure to include the database connection file

if (isset($_SESSION['login_activity_id'])) {
    // Log the logout time for the session
    $query = $pdo->prepare("UPDATE login_activity SET logout_time = NOW() WHERE id = ?");
    $query->execute([$_SESSION['login_activity_id']]);
    unset($_SESSION['login_activity_id']);
}

// Clear all session data and destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit();
?>
