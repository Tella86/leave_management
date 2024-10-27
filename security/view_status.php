<?php
session_start();
include('../includes/db.php');

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user information
$query = $pdo->prepare("SELECT username, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

// Initialize messages
$error = "";
$success = "";

// Fetch all approved leaves
$query = $pdo->prepare("SELECT leave_id, leave_type, start_date, end_date, status, checked_out_at, checked_in_at 
                        FROM leaves 
                        WHERE status = 'Approved'");
$query->execute();
$leaves = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Leave Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a href="../index.php" class="btn btn-secondary mt-3">Dashboard</a>
            <p class="text-light">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <p class="text-light">Role: <?php echo htmlspecialchars($user['role']); ?></p>
            <hr class="bg-light">
            <a href="process_gateman_checkout.php">View Checked-Out Students</a>
            <a href="student_list.php">Check Out/In Student</a>
            <a href="student_list.php">View Student</a>
            <a href="view_status.php">View Status</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <div class="container mt-5">
                <h2 class="text-center mb-4">Approved Leave Applications</h2>
                
                <?php if (count($leaves) > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaves as $leave): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['status']); ?></td>
                                    <td>
                                        <?php if (!$leave['checked_out_at']): ?>
                                            <form action="process_check.php" method="POST">
                                                <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                                <button type="submit" name="action" value="checkout" class="btn btn-primary">Check Out</button>
                                            </form>
                                        <?php elseif ($leave['checked_out_at'] && !$leave['checked_in_at']): ?>
                                            <form action="process_check.php" method="POST">
                                                <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                                <button type="submit" name="action" value="checkin" class="btn btn-success">Check In</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Leave Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No approved leave applications found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>