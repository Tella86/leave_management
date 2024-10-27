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
$query = $pdo->prepare("SELECT username, role, dashboard_color FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

// Update color if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dashboard_color'])) {
    $new_color = $_POST['dashboard_color'];

    // Update the user's preferred dashboard color in the database
    $update_query = $pdo->prepare("UPDATE users SET dashboard_color = ? WHERE user_id = ?");
    $update_query->execute([$new_color, $user_id]);

    // Update the color in the user array
    $user['dashboard_color'] = $new_color;
}

$dashboard_color = $user['dashboard_color'] ?: '#343a40';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Leave Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
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
            background-color: <?php echo htmlspecialchars($dashboard_color); ?>;
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
        .settings-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
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
                <a href="admin/register.php">Register</a>
                <a href="admin/manage_students.php">Manage Students</a>
                <a href="admin/manage_leaves.php">Manage Leave Applications</a>
                <a href="admin/view_reports.php">View Leave Reports</a>
                <a href="admin/leave_countdown.php">Leave Countdown</a>
            <?php elseif ($user['role'] == 'Owner'): ?>
                <a href="owner/view_status.php">View Leave Status</a>
                <a href="owner/approve_leave.php">Approve/Reject Leaves</a>
            <?php elseif ($user['role'] == 'Security'): ?>
                <a href="security/process_gateman_checkout.php">View Checked-Out Students</a>
                <a href="security/student_list.php">Check Out/In Student</a>
                <a href="security/student_list.php">View Student</a>
                <a href="security/view_status.php">View Status</a>
            <?php endif; ?>

            <a href="logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1>Welcome to the Online Leave Management System</h1>

            <!-- Settings Icon -->
            <i class="bi bi-gear settings-icon" data-toggle="modal" data-target="#settingsModal" style="font-size: 24px; cursor: pointer;"></i>

            <!-- Settings Modal -->
            <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="settingsModalLabel">Dashboard Settings</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label for="dashboard_color">Change Dashboard Color:</label>
                                    <input type="color" name="dashboard_color" id="dashboard_color" value="<?php echo htmlspecialchars($dashboard_color); ?>" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

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
                <?php elseif ($user['role'] == 'Security'): ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">View Checked-Out Students</h5>
                                <p class="card-text">View the list of students who are currently checked out and not yet checked back in.</p>
                                <a href="security/process_gateman_checkout.php" class="btn btn-primary">View Students</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Check Out/In Student</h5>
                                <p class="card-text">Check a student out or in at the gate.</p>
                                <a href="security/student_list.php" class="btn btn-info">Check Out/In</a>
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
