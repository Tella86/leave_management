<?php include('../includes/db.php'); ?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../assets/css/styles.css"></head>
<body>
    <h2>Apply for Leave</h2>
    <form action="submit_leave.php" method="POST">
        <label>Leave Type:</label>
        <input type="text" name="leave_type" required>
        
        <label>Start Date:</label>
        <input type="date" name="start_date" required>
        
        <label>End Date:</label>
        <input type="date" name="end_date" required>
        
        <button type="submit">Submit Leave</button>
    </form>
</body>
</html>
