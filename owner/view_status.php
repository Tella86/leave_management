<?php
session_start();
include('../includes/db.php');

// Check if the user is the Owner
$user_id = $_SESSION['user_id'] ?? null;
$query = $pdo->prepare("SELECT username, email, employee_number, photo, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();
if ($user['role'] !== 'Owner') {
    echo "Access denied.";
    exit();
}

// Fetch all leave applications
$query = $pdo->query("SELECT leaves.leave_id, users.username, leaves.leave_type, leaves.start_date, leaves.end_date, leaves.status 
                      FROM leaves 
                      JOIN users ON leaves.user_id = users.user_id
                      ORDER BY leaves.start_date DESC");
$leaves = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
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

    .profile-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
    }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="text-center">
                <?php if (!empty($user['photo'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile Photo"
                    class="profile-photo">
                <?php else: ?>
                <img src="../assets/default-profile.png" alt="Default Profile Photo" class="profile-photo">
                <?php endif; ?>
                <p class="text-light">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
                <a href="../index.php" class="btn btn-secondary mt-3">Dashboard</a>
            </div>
            <hr class="bg-light">
            <a href="view_status.php">View Leave Status</a>
            <a href="approve_leave.php">Approve/Reject Leaves</a>
            <a href="leave_countdown.php">Leave Countdown</a>
            <a href="view_activity.php">View Activity</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

        <!-- Content -->
        <div class="container mt-4">
            <h2>All Leave Applications</h2>

            <?php if (count($leaves) > 0): ?>
            <table class="table table-striped table-bordered mt-3">
                <thead class="table-dark">
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
            <p class="text-muted">No leave applications found.</p>
            <?php endif; ?>

            <a href="../index.php" class="btn btn-primary mt-3">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>