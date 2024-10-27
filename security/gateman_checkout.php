<?php
session_start();
include('../includes/db.php');
// include('../includes/functions.php'); // For checkUserRole function
include('../students/sendSMS.php'); // Ensure sendSMS function is included here

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

$studentInfo = '';
$leaveDetails = '';
$error = '';

// Handle form submission to check leave status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentInput = trim($_POST['student_input']);

    // Query to fetch approved leave details if not checked out
    $query = $pdo->prepare("SELECT leaves.leave_id, leaves.leave_type, leaves.start_date, leaves.end_date, leaves.status, 
                            users.user_id, users.username, users.phone 
                            FROM leaves 
                            JOIN users ON leaves.user_id = users.user_id 
                            WHERE (users.user_id = ? OR users.username LIKE ?) 
                            AND leaves.status = 'Approved' 
                            AND leaves.checked_out_at IS NULL");
    $query->execute([$studentInput, "%$studentInput%"]);
    $leave = $query->fetch();

    if ($leave) {
        // Student's leave and personal information
        $studentInfo = [
            'username' => $leave['username'],
            'User ID' => $leave['user_id'],
            'Phone' => $leave['phone']
        ];
        $leaveDetails = [
            'Leave ID' => $leave['leave_id'],
            'Leave Type' => $leave['leave_type'],
            'Start Date' => $leave['start_date'],
            'End Date' => $leave['end_date'],
            'Status' => $leave['status']
        ];
    } else {
        $error = "No approved leave found or the student has already checked out.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search || Students</title>
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
            <a href="process_gateman_checkout.php">View Checked-Out Students</a>
            <a href="student_list.php">Check Out/In Student</a>
            <a href="student_list.php">View Student</a>
            <a href="view_status.php">View Status</a>
            <a href="../logout.php" class="mt-3 btn btn-danger">Logout</a>
        </nav>
    <div class="container mt-5">
        <h2 class="text-center">Student Check-Out</h2>

        <!-- Form to search student by ID or name -->
        <form action="gateman_checkout.php" method="POST" class="form-inline mb-4">
            <label for="student_input" class="mr-2">Enter Student ID or Name:</label>
            <input type="text" id="student_input" name="student_input" class="form-control mr-2" required>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($studentInfo && $leaveDetails): ?>
            <h3>Student Information</h3>
            <table class="table table-bordered mb-3">
                <tbody>
                    <?php foreach ($studentInfo as $key => $value): ?>
                        <tr>
                            <th><?php echo $key; ?></th>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Leave Details</h3>
            <table class="table table-bordered mb-3">
                <tbody>
                    <?php foreach ($leaveDetails as $key => $value): ?>
                        <tr>
                            <th><?php echo $key; ?></th>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Check-Out Button -->
            <form action="process_gateman_checkout.php" method="POST">
                <input type="hidden" name="leave_id" value="<?php echo $leaveDetails['Leave ID']; ?>">
                <button type="submit" class="btn btn-success">Check Out Student</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
