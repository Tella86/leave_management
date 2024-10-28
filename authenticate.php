<?php
session_start();
include('includes/db.php');

// Initialize error message
$error = "";

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if fields are empty
    if (empty($username) || empty($password)) {
        $error = "Please fill in both username and password.";
    } else {
        // Fetch user details from the database
        $query = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $query->execute([$username]);
        $user = $query->fetch();

        // Verify user and password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Insert a new login activity entry after successful authentication
            $query = $pdo->prepare("INSERT INTO login_activity (user_id, login_time) VALUES (?, NOW())");
            $query->execute([$user['user_id']]);

            // Store the login activity ID in the session for tracking
            $_SESSION['login_activity_id'] = $pdo->lastInsertId();

            // Redirect based on role
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// Redirect back to login page with error message if authentication fails
if ($error) {
    $_SESSION['error'] = $error;
    header("Location: login.php");
    exit();
}
?>
