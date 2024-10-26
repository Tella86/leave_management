<?php
session_start();
include('../includes/db.php');
// include('../includes/functions.php');

// // Check if the user is logged in as an Admin
// checkUserRole('Admin');

// Handle leave approval/rejection
if (isset($_GET['action']) && isset($_GET['leave_id'])) {
    $leave_id = $_GET['leave_id'];
    $action = $_GET['action'] === 'approve' ? 'Approved' : 'Rejected';

    // Update leave status in the database
    $query = $pdo->prepare("UPDATE leaves SET status = ? WHERE leave_id = ?");
    if ($query->execute([$action, $leave_id])) {
        $success = "Leave has been " . strtolower($action) . " successfully!";
    } else {
        $error = "Failed to update leave status. Please try again.";
    }
}

// Fetch all pending leave applications
$leaves = $pdo->query("SELECT leaves.leave_id, users.username, leaves.leave_type, leaves.start_date, leaves.end_date, leaves.status 
                       FROM leaves 
                       JOIN users ON leaves.user_id = users.user_id
                       WHERE leaves.status = 'Pending'")
                       ->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Applications</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage Leave Applications</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

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
                        <th>Actions</th>
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
                            <td>
                                <a href="manage_leaves.php?action=approve&leave_id=<?php echo $leave['leave_id']; ?>" 
                                   onclick="return confirm('Are you sure you want to approve this leave?');">Approve</a> |
                                <a href="manage_leaves.php?action=reject&leave_id=<?php echo $leave['leave_id']; ?>" 
                                   onclick="return confirm('Are you sure you want to reject this leave?');">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending leave applications.</p>
        <?php endif; ?>

        <a href="../index.php">Back to Dashboard</a>
    </div>
</body>
</html>
