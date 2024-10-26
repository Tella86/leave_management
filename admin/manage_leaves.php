<?php
session_start();
include('../includes/db.php');
// include('../includes/functions.php');

// Check if the user is logged in as an Admin
// checkUserRole('Admin');

// Initialize success and error messages
$success = "";
$error = "";

// Handle leave approval or rejection
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Manage Leave Applications</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

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
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Are you sure you want to approve this leave?');">Approve</a>
                                <a href="manage_leaves.php?action=reject&leave_id=<?php echo $leave['leave_id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to reject this leave?');">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No pending leave applications.</p>
        <?php endif; ?>

        <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
