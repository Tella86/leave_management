<?php
session_start();
include('../includes/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = "";

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $leave_type = trim($_POST['leave_type']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (empty($leave_type) || empty($start_date) || empty($end_date)) {
        $error = "Please fill in all fields.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date.";
    } else {
        // Insert leave application into database
        $query = $pdo->prepare("INSERT INTO leaves (user_id, leave_type, start_date, end_date, status) VALUES (?, ?, ?, ?, 'Pending')");
        if ($query->execute([$user_id, $leave_type, $start_date, $end_date])) {
            echo "<div class='alert alert-success'>Leave application submitted successfully! Redirecting to dashboard...</div>";
            header("refresh:3;url=../index.php");
            exit();
        } else {
            $error = "Failed to submit leave application. Please try again.";
        }
    }
}
?>