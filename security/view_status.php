<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT username, role, photo, admission_number FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

// Initialize variables for filters and messages
$error = "";
$status_filter = "";
$start_date = "";
$end_date = "";

// Fetch approved leaves that are either not checked out or checked out but not checked in
$query = $pdo->prepare("SELECT users.photo, users.admission_number, users.username, leave_id, leave_type, start_date, end_date, status, checked_out_at, checked_in_at 
                        FROM leaves 
                        JOIN users ON leaves.user_id = users.user_id 
                        WHERE status = 'Approved' AND (checked_out_at IS NULL OR checked_in_at IS NULL)");
$query->execute();
$leaves = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
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
            <a href="process_gateman_checkout.php">View Checked-Out Students</a>
            <a href="student_list.php">Check Out/In Student</a>
            <a href="student_list.php">View Student</a>
            <a href="view_status.php">View Status</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>
        <!-- Main Content -->
        <div class="content">
            <div class="container mt-5">
                <h2 class="text-center mb-4">Manage Leave Check-Out/Check-In</h2>
                
                <?php if (count($leaves) > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Admin No.</th>
                                <th>Student Name</th>
                                <th>Photo</th>
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
                                    <td><?php echo htmlspecialchars($leave['admission_number']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['username']); ?></td>
                                    <td><img src="../uploads/<?php echo htmlspecialchars($leave['photo']); ?>" width="50" height="50"></td>
                                    <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['status']); ?></td>
                                    <td>
                                        <?php if (!$leave['checked_out_at']): ?>
                                            <!-- Check Out button -->
                                            <form action="process_gate_check.php" method="POST">
                                                <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                                <button type="submit" name="action" value="checkout" class="btn btn-primary">Check Out</button>
                                            </form>
                                        <?php elseif ($leave['checked_out_at'] && !$leave['checked_in_at']): ?>
                                            <!-- Check In button -->
                                            <form action="process_gate_check.php" method="POST">
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
                    <p class="text-muted">No pending check-out or check-in applications found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
