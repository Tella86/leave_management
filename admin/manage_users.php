<?php
session_start();
include('../includes/db.php');
include('../log_action.php');

// Check if the user is an admin
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch user role to confirm if admin
$query = $pdo->prepare("SELECT username, email, employee_number, photo, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

if ($user['role'] !== 'Admin') {
    echo "Access denied.";
    exit();
}

// Fetch users with specific roles (Admin, Owner, Security)
$query = $pdo->prepare("SELECT user_id, username, email, employee_number, photo, role FROM users WHERE role IN ('Admin', 'Owner', 'Security')");
$query->execute();
$users = $query->fetchAll();

// Handle form submission for updating a user
$error = "";
$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id_to_update = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $employee_number = trim($_POST['employee_number']);
    $photo = $_FILES['photo'];

    if (empty($username) || empty($role)) {
        $error = "Please fill in all required fields.";
    } else {
        // Update user details and photo if uploaded
        if ($photo['size'] > 0) {
            $photo_path = '../uploads/' . basename($photo['name']);
            move_uploaded_file($photo['tmp_name'], $photo_path);

            $query = $pdo->prepare("UPDATE users SET username = ?, email=?, role = ?, employee_number = ?, photo = ? WHERE user_id = ?");
            $query->execute([$username, $email, $role, $employee_number, $photo['name'], $user_id_to_update]);
        } else {
            $query = $pdo->prepare("UPDATE users SET username = ?, email=?, role = ?, employee_number = ? WHERE user_id = ?");
            $query->execute([$username, $email, $role, $employee_number, $user_id_to_update]);
        }

        $success = "User updated successfully.";
    }
}
  // Log the activity
//   logAction($pdo, $user_id, "Added a new user with username: $username");
  
// Handle delete user request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $query->execute([$delete_id]);
    $success = "User deleted successfully.";
    header("Location: manage_users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User ||Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
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
  <!-- Settings Icon -->
           
  <a href="logout.php" class="logout-btn btn btn-danger">Logout</a>

<!-- Settings Icon -->
<i class="bi bi-gear settings-icon" data-toggle="modal" data-target="#settingsModal" style="font-size: 24px; cursor: pointer;"></i>

            <!-- Settings Modal -->
            <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
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
                                    <input type="color" name="dashboard_color" id="dashboard_color" value="<?php echo htmlspecialchars($dashboard_color); ?>" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    <div class="container mt-5">
        <h2 class="text-center mb-4">User Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Users Table -->
        <h4>All Users</h4>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Employee Number</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Photo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['employee_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><img src="../uploads/<?php echo htmlspecialchars($user['photo']); ?>" width="50" height="50"></td>
                        <td>
                            <!-- Edit Button to trigger modal -->
                            <button class="btn btn-warning btn-sm" onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['role']; ?>', '<?php echo $user['employee_number']; ?>', '<?php echo $user['photo']; ?>')">Edit</button>

                            <!-- Delete Button -->
                            <a href="manage_users.php?delete_id=<?php echo $user['user_id']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="manage_users.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="user_id" id="user_id">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="text" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="employee_number" class="form-label">Employee Number:</label>
                                <input type="text" name="employee_number" id="employee_number" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role:</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Owner">Owner</option>
                                    <option value="Security">Security</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Profile Photo:</label>
                                <input type="file" name="photo" id="photo" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="update_user" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(id, username, role, employee_number, photo) {
            document.getElementById('user_id').value = id;
            document.getElementById('username').value = username;
            document.getElementById('role').value = role;
            document.getElementById('employee_number').value = employee_number;
            var editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editUserModal.show();
        }
    </script>
</body>
</html>
