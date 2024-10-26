<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Check if the user is logged in as an Admin
checkUserRole('Admin');

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
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Leave Reports</h2>

        <h3>Filter Leave Reports</h3>
        <form action="view_reports.php" method="POST">
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="">All</option>
                <option value="Pending" <?php if ($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="Approved" <?php if ($status_filter == 'Approved') echo 'selected'; ?>>Approved</option>
                <option value="Rejected" <?php if ($status_filter == 'Rejected') echo 'selected'; ?>>Rejected</option>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

            <button type="submit">Filter</button>
        </form>

        <h3>Leave Applications Report</h3>
        <?php if (count($leaves) > 0): ?>
            <table>
                <thead>
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
            <p>No leave applications found for the selected filters.</p>
        <?php endif; ?>

        <a href="../index.php">Back to Dashboard</a>
    </div>
</body>
</html>
