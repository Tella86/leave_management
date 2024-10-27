<?php
session_start();
include('../includes/db.php');
include('../students/sendSMS.php'); // Ensure sendSMS function is included here

// Check if the user is logged in as a gateman
// checkUserRole('Gateman');

$leave_id = $_POST['leave_id'];

// Update the check-out time for the student
$query = $pdo->prepare("UPDATE leaves SET checked_out_at = NOW() WHERE leave_id = ?");
if ($query->execute([$leave_id])) {
    // Fetch parent's phone number
    $query = $pdo->prepare("SELECT users.phone FROM leaves JOIN users ON leaves.user_id = users.user_id WHERE leave_id = ?");
    $query->execute([$leave_id]);
    $parent = $query->fetch();

    if ($parent && $parent['phone']) {
        $message = "Dear Parent, your child has checked out of the college.";
        sendSMS($parent['phone'], $message, true, 'checkout'); // Send SMS notification to the parent
    }

    header("Location: gateman_checkout.php?success=Student checked out successfully.");
    exit();
} else {
    die("Error: Could not update the check-out status.");
}
