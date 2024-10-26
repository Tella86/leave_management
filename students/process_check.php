<?php
session_start();
include('../includes/db.php');
include('sendSMS.php'); // Adjust this path as necessary

$user_id = $_SESSION['user_id'];
$leave_id = $_POST['leave_id'];
$action = $_POST['action'];

// Fetch leave details and parent phone number from the users table
$query = $pdo->prepare("SELECT leaves.*, users.phone AS parent_phone 
                        FROM leaves 
                        JOIN users ON leaves.user_id = users.user_id 
                        WHERE leaves.leave_id = ? AND leaves.user_id = ?");
$query->execute([$leave_id, $user_id]);
$leave = $query->fetch();

if (!$leave) {
    die("Invalid leave application.");
}

// Check-out process
if ($action === 'checkout' && !$leave['checked_out_at']) {
    // Record check-out time
    $query = $pdo->prepare("UPDATE leaves SET checked_out_at = NOW() WHERE leave_id = ?");
    $query->execute([$leave_id]);

    // Send SMS notification to parent
    if (!empty($leave['parent_phone'])) {
        $message = "Dear Parent, your child has checked out of the college.";
        sendSMS($leave['parent_phone'], $message);
    }

    header("Location: view_status.php");
    exit();

// Check-in process
} elseif ($action === 'checkin' && $leave['checked_out_at'] && !$leave['checked_in_at']) {
    // Record check-in time
    $query = $pdo->prepare("UPDATE leaves SET checked_in_at = NOW() WHERE leave_id = ?");
    $query->execute([$leave_id]);

    // Send SMS notification to parent
    if (!empty($leave['parent_phone'])) {
        $message = "Dear Parent, your child has checked back into the college.";
        sendSMS($leave['parent_phone'], $message);
    }

    header("Location: view_status.php");
    exit();

} else {
    die("Invalid action.");
}
