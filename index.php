<?php
session_start();
include('includes/db.php');

// Check if the user is logged in; if not, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT username, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Leave Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">Welcome to the Online Leave Management System</h1>
        <p class="text-center">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
        <p class="text-center">Your role: <?php echo htmlspecialchars($user['role']); ?></p>

        <nav class="text-center my-4">
            <?php if ($user['role'] == 'Student'): ?>
                <a href="student/apply_leave.php" class="btn btn-primary mx-2">Apply for Leave</a>
                <a href="student/view_status.php" class="btn btn-info mx-2">View Leave Status</a>
            <?php elseif ($user['role'] == 'Admin'): ?>
                <a href="admin/manage_departments.php" class="btn btn-primary mx-2">Manage Departments</a>
                <a href="admin/manage_students.php" class="btn btn-info mx-2">Manage Students</a>
                <a href="admin/manage_leaves.php" class="btn btn-warning mx-2">Manage Leave Applications</a>
                <a href="admin/view_reports.php" class="btn btn-secondary mx-2">View Leave Reports</a>
            <?php elseif ($user['role'] == 'Owner'): ?>
                <a href="owner/view_status.php" class="btn btn-primary mx-2">View Leave Status</a>
                <a href="owner/approve_leave.php" class="btn btn-success mx-2">Approve/Reject Leaves</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger mx-2">Logout</a>
        </nav>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
