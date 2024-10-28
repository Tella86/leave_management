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

// Pagination setup
$limit = 5; // number of departments per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of departments for pagination
$total_query = $pdo->query("SELECT COUNT(*) as total FROM departments");
$total = $total_query->fetchColumn();
$total_pages = ceil($total / $limit);

// Fetch limited departments for the current page
$departments = $pdo->prepare("SELECT * FROM departments LIMIT ? OFFSET ?");
$departments->bindValue(1, $limit, PDO::PARAM_INT);
$departments->bindValue(2, $offset, PDO::PARAM_INT);
$departments->execute();
$departments = $departments->fetchAll();

// Handle adding a new department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_department'])) {
    $dept_name = trim($_POST['dept_name']);
    if (empty($dept_name)) {
        $error = "Please enter a department name.";
    } else {
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
    $query = $pdo->prepare("DELETE FROM departments WHERE dept_id = ?");
    if ($query->execute([$dept_id])) {
        $success = "Department deleted successfully!";
    } else {
        $error = "Failed to delete department. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage || Departments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/ezems.css">
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
            <a href="manage_departments.php">Manage Departments</a>
            <a href="register.php">Register</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_leaves.php">Manage Leave Applications</a>
            <a href="view_reports.php">View Leave Reports</a>
            <a href="leave_countdown.php">Leave Countdown</a>
            <a href="profile.php">Profile</a>
            <!-- <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a> -->
        </nav>

        <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Departments</h2>
            <!-- Settings Icon -->

            <a href="logout.php" class="logout-btn btn btn-danger">Logout</a>

            <!-- Settings Icon -->
            <i class="bi bi-gear settings-icon" data-toggle="modal" data-target="#settingsModal"
                style="font-size: 24px; cursor: pointer;"></i>

            <!-- Settings Modal -->
            <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="settingsModalLabel">Dashboard Settings</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label for="dashboard_color">Change Dashboard Color:</label>
                                    <input type="color" name="dashboard_color" id="dashboard_color"
                                        value="<?php echo htmlspecialchars($dashboard_color); ?>" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container mt-5">
            

                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                    data-bs-target="#addDepartmentModal">
                    Add Department
                </button>

                <!-- Add Department Modal -->
                <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="manage_departments.php" method="POST">
                                    <div class="mb-3">
                                        <label for="dept_name" class="form-label">Department Name:</label>
                                        <input type="text" id="dept_name" name="dept_name" class="form-control"
                                            required>
                                    </div>
                                    <button type="submit" name="add_department" class="btn btn-primary">Add
                                        Department</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <h3>Existing Departments</h3>
                <?php if (count($departments) > 0): ?>
                <table class="table table-striped table-bordered">
                    <thead>
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
                <!-- Pagination -->
                <nav>
                    <ul class="pagination">
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                        <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php else: ?>
                <p class="text-muted">No departments found.</p>
                <?php endif; ?>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>