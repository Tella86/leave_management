<?php
session_start();
include('../includes/db.php');

// Check if the user is logged in; if not, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT username, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

// Initialize messages
$error = "";
$success = "";

// Handle adding a new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // Validate form input
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if the username already exists
        $query = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $query->execute([$username]);

        if ($query->rowCount() > 0) {
            $error = "Username already taken. Please choose another.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new student into the database with the role 'Student'
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'Student')");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = "Student added successfully!";
            } else {
                $error = "Failed to add student. Please try again.";
            }
        }
    }
}

// Handle deleting a student
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];

    // Delete student from database
    $query = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Student'");
    if ($query->execute([$user_id])) {
        $success = "Student deleted successfully!";
    } else {
        $error = "Failed to delete student. Please try again.";
    }
}

// Fetch all students
$students = $pdo->query("SELECT * FROM users WHERE role = 'Student'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <a href="../index.php" class="btn btn-secondary mt-3">Dashboard</a>
            <p class="text-light">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <p class="text-light">Role: <?php echo htmlspecialchars($user['role']); ?></p>
            <hr class="bg-light">
            <hr class="bg-light">
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_departments.php">Manage Departments</a>
            <a href="manage_leaves.php">Manage Leave Applications</a>
            <a href="view_reports.php">View Leave Reports</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <div class="container mt-4">
                <h2 class="text-center mb-4">Manage Students</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Button to open the Add Student modal -->
                <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    Add Student
                </button>

                <h3>Existing Students</h3>
                <?php if (count($students) > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <a href="manage_students.php?delete_id=<?php echo $student['user_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No students found.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_students.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone:</label>
                            <input type="phone" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
