<?php
session_start();
include('../includes/db.php');
// include('../includes/functions.php');

// Check if the user is logged in as an Admin
// checkUserRole('Admin');

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
$query = "SELECT leaves.leave_id, users.username, leaves.leave_type, leaves.start_date, leaves.end_date, leaves.status 
          FROM leaves 
          JOIN users ON leaves.user_id = users.user_id
          WHERE 1 = 1";

$params = [];
if ($status_filter) {
    $query .= " AND leaves.status = ?";
    $params[] = $status_filter;
}
if ($start_date) {
    $query .= " AND leaves.start_date >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $query .= " AND leaves.end_date <= ?";
    $params[] = $end_date;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$leaves = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No leave applications found for the selected filters.</p>
        <?php endif; ?>

        <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
