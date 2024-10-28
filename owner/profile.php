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

// Initialize an empty error message
$error = "";
$success = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if current password is correct
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password in the database
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($update->execute([$hashed_password, $user_id])) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            } else {
                $error = "New password and confirm password do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
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
        <!-- Main Content -->
        <div class="content">
            <div class="container mt-5">
                <h2 class="mb-4">Profile Management</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h3>Profile Details</h3>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Admin No.:</strong> <?php echo htmlspecialchars($user['employee_number']); ?></p>

                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3>Change Password</h3>
                        <form action="profile.php" method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password:</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password:</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-primary" name="update_password">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
