<?php
session_start();
include('../includes/db.php');
// include('../includes/functions.php');

// Check if the user is logged in as an Admin
// checkUserRole('Admin');

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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Manage Departments</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <h3>Add New Department</h3>
        <form action="manage_departments.php" method="POST" class="form-inline mb-4">
            <div class="form-group mr-2">
                <label for="dept_name" class="mr-2">Department Name:</label>
                <input type="text" id="dept_name" name="dept_name" class="form-control" required>
            </div>
            <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
        </form>

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

        <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <!-- Optional JavaScript for Bootstrap functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
