<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT username, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();
// Initialize variables for filters and messages
$error = "";
$status_filter = "";
$start_date = "";
$end_date = "";

// Handle form submission for filtering
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status_filter = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

// Build the query with filters
$query = $pdo->query("SELECT leaves.leave_id, users.username, leaves.leave_type, leaves.start_date, leaves.end_date, leaves.status, leaves.checked_out_at, leaves.checked_in_at 
                      FROM leaves 
                      JOIN users ON leaves.user_id = users.user_id");
$leaves = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_departments.php">Manage Departments</a>
            <a href="manage_leaves.php">Manage Leave Applications</a>
            <a href="view_reports.php">View Leave Reports</a>
            <a href="leave_countdown.php">Leave Countdown</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Leave Reports</h2>

        <h3>Filter Leave Reports</h3>
        <form action="view_reports.php" method="POST" class="form-inline mb-4">
            <div class="form-group mr-2">
                <label for="status" class="mr-2">Status:</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All</option>
                    <option value="Pending" <?php if ($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Approved" <?php if ($status_filter == 'Approved') echo 'selected'; ?>>Approved</option>
                    <option value="Rejected" <?php if ($status_filter == 'Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <label for="start_date" class="mr-2">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="form-group mr-2">
                <label for="end_date" class="mr-2">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <h3>Leave Applications Report</h3>
        <?php if (count($leaves) > 0): ?>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Leave ID</th>
                        <th>Student Name</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Checked Out</th>
                        <th>Checked In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leave['leave_id']); ?></td>
                            <td><?php echo htmlspecialchars($leave['username']); ?></td>
                            <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($leave['status']); ?></td>
                            <td><?php echo $leave['checked_out_at'] ? $leave['checked_out_at'] : 'Not checked out'; ?></td>
                            <td><?php echo $leave['checked_in_at'] ? $leave['checked_in_at'] : 'Not checked in'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No leave applications found for the selected filters.</p>
        <?php endif; ?>

        <!-- <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a> -->
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
