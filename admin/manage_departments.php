<?php
session_start();
include('../includes/db.php');

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

// Handle adding a new department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_department'])) {
    $dept_name = trim($_POST['dept_name']);

    // Check if the department name is empty
    if (empty($dept_name)) {
        $error = "Please enter a department name.";
    } else {
        // Insert department into database
        $query = $pdo->prepare("INSERT INTO departments (dept_name) VALUES (?)");
        if ($query->execute([$dept_name])) {
            $success = "Department added successfully!";
        } else {
            $error = "Failed to add department. Please try again.";
        }
    }
}

// Handle deleting a department
if (isset($_GET['delete_id'])) {
    $dept_id = $_GET['delete_id'];

    // Delete department from database
    $query = $pdo->prepare("DELETE FROM departments WHERE dept_id = ?");
    if ($query->execute([$dept_id])) {
        $success = "Department deleted successfully!";
    } else {
        $error = "Failed to delete department. Please try again.";
    }
}

// Fetch all departments
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
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
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_departments.php">Manage Departments</a>
            <a href="manage_leaves.php">Manage Leave Applications</a>
            <a href="view_reports.php">View Leave Reports</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <div class="container mt-4">
                <h2 class="text-center mb-4">Manage Departments</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Button to trigger modal -->
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                    Add Department
                </button>

                <!-- Add Department Modal -->
                <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="manage_departments.php" method="POST">
                                    <div class="mb-3">
                                        <label for="dept_name" class="form-label">Department Name:</label>
                                        <input type="text" id="dept_name" name="dept_name" class="form-control" required>
                                    </div>
                                    <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <h3>Existing Departments</h3>
                <?php if (count($departments) > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Department ID</th>
                                <th>Department Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $department): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($department['dept_id']); ?></td>
                                    <td><?php echo htmlspecialchars($department['dept_name']); ?></td>
                                    <td>
                                        <a href="manage_departments.php?delete_id=<?php echo $department['dept_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No departments found.</p>
                <?php endif; ?>

                
            </div>
        </div>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
