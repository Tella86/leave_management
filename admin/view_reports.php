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

// Initialize variables for filters and messages
$error = "";
$status_filter = "";
$start_date = "";
$end_date = "";

// Handle form submission for filtering
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status_filter = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

// Build the query with filters
$queryStr = "SELECT leaves.leave_id, users.username, users.photo, users.admission_number, leaves.leave_type, leaves.start_date, leaves.end_date, leaves.status, leaves.checked_out_at, leaves.checked_in_at 
             FROM leaves 
             JOIN users ON leaves.user_id = users.user_id 
             WHERE 1=1";

$conditions = [];
$params = [];

if ($status_filter) {
    $conditions[] = "leaves.status = ?";
    $params[] = $status_filter;
}

if ($start_date) {
    $conditions[] = "leaves.start_date >= ?";
    $params[] = $start_date;
}

if ($end_date) {
    $conditions[] = "leaves.end_date <= ?";
    $params[] = $end_date;
}

if (!empty($conditions)) {
    $queryStr .= " AND " . implode(" AND ", $conditions);
}

$query = $pdo->prepare($queryStr);
$query->execute($params);
$leaves = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave || Reports</title>
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
        <h2 class="text-center mb-4">Leave Reports</h2>
         
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

            <!-- Button to trigger filter modal -->
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                Filter Leave Reports
            </button>

            <!-- Filter Modal -->
            <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filterModalLabel">Filter Leave Reports</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="view_reports.php" method="POST">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status:</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="">All</option>
                                        <option value="Pending" <?php if ($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Approved" <?php if ($status_filter == 'Approved') echo 'selected'; ?>>Approved</option>
                                        <option value="Rejected" <?php if ($status_filter == 'Rejected') echo 'selected'; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date:</label>
                                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date:</label>
                                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Apply Filter</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Leave Applications Report</h3>
            <?php if (count($leaves) > 0): ?>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Admin No.</th>
                        <th>Student Name</th>
                        <th>Photo</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Checked Out</th>
                        <th>Checked In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($leave['admission_number']); ?></td>
                        <td><?php echo htmlspecialchars($leave['username']); ?></td>
                        <td><img src="../uploads/<?php echo htmlspecialchars($leave['photo']); ?>" width="50" height="50"></td>
                        <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($leave['status']); ?></td>
                        <td><?php echo $leave['checked_out_at'] ? $leave['checked_out_at'] : 'Not checked out'; ?></td>
                        <td><?php echo $leave['checked_in_at'] ? $leave['checked_in_at'] : 'Not checked in'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-muted">No leave applications found for the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
