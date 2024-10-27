<?php
session_start();
include('../includes/db.php');
include('../students/sendSMS.php'); // Adjust this path if necessary

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];

    // Fetch the leave details and parent phone number
    $query = $pdo->prepare("SELECT leaves.*, users.phone AS parent_phone 
                            FROM leaves 
                            JOIN users ON leaves.user_id = users.user_id 
                            WHERE leaves.leave_id = ?");
    $query->execute([$leave_id]);
    $leave = $query->fetch();

    if (!$leave) {
        die("Invalid leave application. Please verify that the leave ID is correct.");
    }

    if ($action === 'checkout' && !$leave['checked_out_at']) {
        // Mark as checked out
        $query = $pdo->prepare("UPDATE leaves SET checked_out_at = NOW() WHERE leave_id = ? AND checked_out_at IS NULL");
        $query->execute([$leave_id]);

        // Send SMS notification to parent
        if (!empty($leave['parent_phone'])) {
            $message = "Dear Parent, your child has checked out of the college. Shanzu TTC.";
            sendSMS($leave['parent_phone'], $message);
        }

    } elseif ($action === 'checkin' && $leave['checked_out_at'] && !$leave['checked_in_at']) {
        // Mark as checked in
        $query = $pdo->prepare("UPDATE leaves SET checked_in_at = NOW() WHERE leave_id = ? AND checked_in_at IS NULL");
        $query->execute([$leave_id]);

        // Send SMS notification to parent
        if (!empty($leave['parent_phone'])) {
            $message = "Dear Parent, your child has checked back into the college. Shanzu TTC.";
            sendSMS($leave['parent_phone'], $message);
        }
    }

    header("Location: view_status.php");
    exit();
}
?>
