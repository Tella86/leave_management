<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Check if the user is logged in as a Student
checkUserRole('Student');

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch leave applications for the logged-in student
$query = $pdo->prepare("SELECT leave_type, start_date, end_date, status FROM leaves WHERE user_id = ?");
$query->execute([$user_id]);
$leaves = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Leave Status</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Leave Application Status</h2>
        
        <?php if (count($leaves) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($leave['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No leave applications found.</p>
        <?php endif; ?>

        <a href="../index.php">Back to Dashboard</a>
    </div>
</body>
</html>
