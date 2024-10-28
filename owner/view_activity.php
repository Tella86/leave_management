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

// Fetch login activity with user actions
$query = $pdo->query("
    SELECT 
        u.username, 
        la.login_time, 
        la.logout_time, 
        al.activity_description, 
        al.timestamp 
    FROM login_activity la
    JOIN users u ON la.user_id = u.user_id
    LEFT JOIN activity_logs al ON la.user_id = al.user_id AND al.timestamp BETWEEN la.login_time AND la.logout_time
    ORDER BY la.login_time DESC, al.timestamp
");
$activities = $query->fetchAll();
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

    .content {
        flex-grow: 1;
        padding: 20px;
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
        <div class="container mt-5">
            <h2 class="text-center mb-4">User Activity Logs</h2>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Activity</th>
                        <th>Activity Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['username']); ?></td>
                        <td><?php echo htmlspecialchars($activity['login_time']); ?></td>
                        <td><?php echo htmlspecialchars($activity['logout_time'] ?? 'Still logged in'); ?></td>
                        <td><?php echo htmlspecialchars($activity['activity_description'] ?? 'No activity recorded'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($activity['timestamp'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>