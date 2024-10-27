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

// Initialize messages
$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Check if fields are empty
    if (empty($username) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if username already exists
        $query = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $query->execute([$username]);
        if ($query->rowCount() > 0) {
            $error = "Username already taken. Please choose another.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user data into the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $role])) {
                $success = "User registered successfully!";
            } else {
                $error = "Registration failed. Please try again.";
            }
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

        <!-- Main Content -->
        <div class="content container">
            <h2>Register a New User</h2>

            <!-- Display success or error message -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="register.php" method="POST" class="mx-auto" style="max-width: 500px;">
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="text" id="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role:</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="Student">Student</option>
                        <option value="Admin">Admin</option>
                        <option value="Owner">Owner</option>
                        <option value="Security">Security</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <p class="text-center mt-3">Already have an account? <a href="../login.php">Login here</a>.</p>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
