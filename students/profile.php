<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Check if the user is logged in as a Student
checkUserRole('Student');

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the user's profile data
$query = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

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
    <title>Manage Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Profile Management</h2>
        
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <h3>Profile Details</h3>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

        <h3>Change Password</h3>
        <form action="profile.php" method="POST">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="update_password">Update Password</button>
        </form>

        <a href="../index.php">Back to Dashboard</a>
    </div>
</body>
</html>
