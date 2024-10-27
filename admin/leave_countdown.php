<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT username, email, admission_number, photo, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

// Fetch approved leaves that have been checked out but not checked in
$query = $pdo->prepare("SELECT leaves.leave_id, leaves.leave_type, leaves.start_date, leaves.end_date, 
                        users.username, users.phone
                        FROM leaves 
                        JOIN users ON leaves.user_id = users.user_id 
                        WHERE leaves.status = 'Approved' AND leaves.checked_out_at IS NOT NULL AND leaves.checked_in_at IS NULL");
$query->execute();
$leaves = $query->fetchAll();

$current_time = new DateTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Countdown and Notification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .expired { color: red; font-weight: bold; }
        .countdown { color: green; font-weight: bold; }
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
                    <img src="../uploads/<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile Photo" class="profile-photo">
                <?php else: ?>
                    <img src="../assets/default-profile.png" alt="Default Profile Photo" class="profile-photo">
                <?php endif; ?>
            <p class="text-light">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <a href="../index.php" class="btn btn-secondary mt-3">Dashboard</a>
            </div>
            <hr class="bg-light">
            <a href="manage_departments.php">Manage Departments</a>
            <a href="register.php">Register</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_leaves.php">Manage Leave Applications</a>
            <a href="view_reports.php">View Leave Reports</a>
            <a href="leave_countdown.php">Leave Countdown</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

<div class="container mt-5">
    <h2 class="text-center mb-4">Leave Duration Countdown and Notification</h2>

    <?php if (count($leaves) > 0): ?>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Leave Type</th>
                    <th>End Date</th>
                    <th>Countdown</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $leave): ?>
                    <?php 
                        // Calculate initial time difference in seconds
                        $end_date = new DateTime($leave['end_date']);
                        $time_left = $end_date->getTimestamp() - $current_time->getTimestamp();
                        $is_expired = $time_left <= 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($leave['username']); ?></td>
                        <td><?php echo htmlspecialchars($leave['phone']); ?></td>
                        <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                        <td id="countdown-<?php echo $leave['leave_id']; ?>" class="<?php echo $is_expired ? 'expired' : 'countdown'; ?>">
                            <?php echo $is_expired ? "Expired" : ""; ?>
                        </td>
                    </tr>
                    <script>
                        // JavaScript to update countdown in real time
                        (function() {
                            const countdownElement = document.getElementById("countdown-<?php echo $leave['leave_id']; ?>");
                            let timeLeft = <?php echo $time_left; ?>; // initial time difference in seconds

                            if (timeLeft > 0) {
                                const countdownInterval = setInterval(() => {
                                    // Calculate days, hours, minutes, and seconds
                                    const days = Math.floor(timeLeft / (60 * 60 * 24));
                                    const hours = Math.floor((timeLeft % (60 * 60 * 24)) / (60 * 60));
                                    const minutes = Math.floor((timeLeft % (60 * 60)) / 60);
                                    const seconds = timeLeft % 60;

                                    countdownElement.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;

                                    timeLeft--;

                                    if (timeLeft < 0) {
                                        clearInterval(countdownInterval);
                                        countdownElement.textContent = "Expired";
                                        countdownElement.classList.remove("countdown");
                                        countdownElement.classList.add("expired");
                                    }
                                }, 1000);
                            } else {
                                countdownElement.textContent = "Expired";
                                countdownElement.classList.add("expired");
                            }
                        })();
                    </script>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No active leave applications found that are pending check-in.</p>
    <?php endif; ?>
</div>

<!-- Optional JavaScript for Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
