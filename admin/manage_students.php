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
$query = $pdo->prepare("SELECT username, email, admission_number, photo, role FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();


// Initialize messages
$error = "";
$success = "";

// Handle adding a new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    // ... Existing code for adding a new student ...
}

// Handle editing a student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $edit_user_id = $_POST['edit_user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $admission_number = trim($_POST['admission_number']);
    $photo_name = $_POST['current_photo'];

    // Check if a new photo is uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = $_FILES['photo'];
        $photo_name = uniqid() . "_" . basename($photo['name']);
        $photo_path = "../uploads/" . $photo_name;

        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($photo['type'], $allowed_types) && $photo['size'] <= 2 * 1024 * 1024) {
            if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
                $error = "Failed to upload new photo.";
            }
        } else {
            $error = "Invalid file type or size too large.";
        }
    }

    // Update the student information in the database
    if (!$error) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, admission_number = ?, photo = ? WHERE user_id = ?");
        if ($stmt->execute([$username, $email, $phone, $admission_number, $photo_name, $edit_user_id])) {
            $success = "Student details updated successfully!";
        } else {
            $error = "Failed to update student details. Please try again.";
        }
    }

    if (isset($_GET['delete_id'])) {
        $delete_id = filter_var($_GET['delete_id'], FILTER_VALIDATE_INT);
    
        if ($delete_id) {
            $query = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Student'");
            if ($query->execute([$delete_id])) {
                header("Location: manage_students.php"); // Refresh page after deletion
                exit();
            } else {
                $error = "Failed to delete student. Error: " . implode(" ", $query->errorInfo());
            }
        } else {
            $error = "Invalid user ID.";
        }
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
    <title>Manage || Students</title>
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
        <h2 class="text-center mb-4">Manage Students</h2>
 
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
        <div class="container mt-4">

            <!-- Display success or error message -->
            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Add Student button and table as before -->

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Admission Number</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                        <td><img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" width="50"
                                height="50"></td>
                        <td>
                            <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editStudentModal<?php echo $student['user_id']; ?>">Edit</a>
                            <a href="manage_students.php?delete_id=<?php echo $student['user_id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>

                        </td>
                    </tr>

                    <!-- Edit Student Modal -->
                    <div class="modal fade" id="editStudentModal<?php echo $student['user_id']; ?>" tabindex="-1"
                        aria-labelledby="editStudentModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="manage_students.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="edit_user_id"
                                            value="<?php echo $student['user_id']; ?>">
                                        <input type="hidden" name="current_photo"
                                            value="<?php echo htmlspecialchars($student['photo']); ?>">

                                        <label>Username:</label>
                                        <input type="text" name="username" class="form-control"
                                            value="<?php echo htmlspecialchars($student['username']); ?>" required>

                                        <label>Email:</label>
                                        <input type="email" name="email" class="form-control"
                                            value="<?php echo htmlspecialchars($student['email']); ?>" required>

                                        <label>Phone:</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="<?php echo htmlspecialchars($student['phone']); ?>">

                                        <label>Admission Number:</label>
                                        <input type="text" name="admission_number" class="form-control"
                                            value="<?php echo htmlspecialchars($student['admission_number']); ?>"
                                            required>

                                        <label>Photo:</label>
                                        <input type="file" name="photo" class="form-control">
                                        <small>Leave blank if you do not wish to change the photo.</small>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="edit_student" class="btn btn-primary">Save
                                            Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>