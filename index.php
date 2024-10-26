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
    <style>
        /* Custom styles for sidebar layout */
        .wrapper {
            display: flex;
            width: 100%;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #007bff;
            color: white;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2 class="text-light">Dashboard</h2>
            <p class="text-light">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <p class="text-light">Role: <?php echo htmlspecialchars($user['role']); ?></p>
            <hr class="bg-light">

            <?php if ($user['role'] == 'Student'): ?>
                <a href="students/apply_leave.php">Apply for Leave</a>
                <a href="students/view_status.php">View Leave Status</a>
                <a href="students/profile.php">Profile</a>
            <?php elseif ($user['role'] == 'Admin'): ?>
                <a href="admin/manage_departments.php">Manage Departments</a>
                <a href="admin/manage_students.php">Manage Students</a>
                <a href="admin/manage_leaves.php">Manage Leave Applications</a>
                <a href="admin/view_reports.php">View Leave Reports</a>
            <?php elseif ($user['role'] == 'Owner'): ?>
                <a href="owner/view_status.php">View Leave Status</a>
                <a href="owner/approve_leave.php">Approve/Reject Leaves</a>
            <?php endif; ?>

            <a href="logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1>Welcome to the Online Leave Management System</h1>

            <!-- Summary Cards -->
            <div class="row">
                <?php if ($user['role'] == 'Student'): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Apply for Leave</h5>
                                <p class="card-text">Submit a new leave application for review.</p>
                                <a href="students/apply_leave.php" class="btn btn-primary">Apply Now</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">View Leave Status</h5>
                                <p class="card-text">Check the status of your leave applications.</p>
                                <a href="students/view_status.php" class="btn btn-info">View Status</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Profile</h5>
                                <p class="card-text">Update your profile and account settings.</p>
                                <a href="students/profile.php" class="btn btn-info">Update Profile</a>
                            </div>
                        </div>
                    </div>
                <?php elseif ($user['role'] == 'Admin'): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Manage Departments</h5>
                                <p class="card-text">Create and manage different departments in the system.</p>
                                <a href="admin/manage_departments.php" class="btn btn-primary">Manage Departments</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Manage Students</h5>
                                <p class="card-text">View and update student profiles and leave requests.</p>
                                <a href="admin/manage_students.php" class="btn btn-info">Manage Students</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Manage Leave Applications</h5>
                                <p class="card-text">Review and approve/reject leave applications submitted by students.</p>
                                <a href="admin/manage_leaves.php" class="btn btn-warning">Manage Leave Applications</a>
                            </div>
                        </div>
                    </div>
                <?php elseif ($user['role'] == 'Owner'): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">View Leave Status</h5>
                                <p class="card-text">View the overall leave status of students and employees.</p>
                                <a href="owner/view_status.php" class="btn btn-primary">View Status</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Approve/Reject Leaves</h5>
                                <p class="card-text">Review and approve or reject leave applications.</p>
                                <a href="owner/approve_leave.php" class="btn btn-success">Approve/Reject Leaves</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
