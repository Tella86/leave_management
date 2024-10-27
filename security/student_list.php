<?php
session_start();
include('../includes/db.php');
// include('../includes/functions.php'); // For checkUserRole function

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

// Fetch list of students who have checked out but not checked in
$query = $pdo->prepare("SELECT leaves.leave_id, users.admission_number, leaves.leave_type, leaves.start_date, leaves.end_date, 
                        leaves.checked_out_at, leaves.checked_in_at, users.user_id, users.username, users.phone 
                        FROM leaves 
                        JOIN users ON leaves.user_id = users.user_id 
                        WHERE leaves.checked_out_at IS NOT NULL AND leaves.status = 'Approved'");
$query->execute();
$checkedOutStudents = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* Custom styles for sidebar layout */
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
            <a href="process_gateman_checkout.php">View Checked-Out Students</a>
            <a href="student_list.php">Check Out/In Student</a>
            <a href="student_list.php">View Student</a>
            <a href="view_status.php">View Status</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>
    <div class="container mt-5">
        <h2 class="text-center">Security Dashboard</h2>
        <h3>Students Currently Checked Out</h3>

        <?php if (count($checkedOutStudents) > 0): ?>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Admin No.</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Checked Out At</th>
                        <th>Checked In At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checkedOutStudents as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            <td><?php echo htmlspecialchars($student['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($student['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($student['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($student['checked_out_at']); ?></td>
                            <td>
                                <?php
                                if ($student['checked_in_at']) {
                                    echo htmlspecialchars($student['checked_in_at']);
                                } else {
                                    echo "<span class='text-danger'>Still Out</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No students currently checked out.</p>
        <?php endif; ?>

        <a href="gateman_checkout.php" class="btn btn-primary mt-3">Check Out New Student</a>
    </div>
</body>
</html>
