<?php
include('../includes/db.php');

header('Content-Type: application/json');

// Initial pending count
$initialPendingCount = isset($_GET['initial_count']) ? intval($_GET['initial_count']) : 0;
$newPendingCount = $initialPendingCount;

while (true) {
    // Fetch the current count of pending leave applications
    $query = $pdo->query("SELECT COUNT(*) AS pending_count FROM leaves WHERE status = 'Pending'");
    $newPendingCount = $query->fetchColumn();

    // If new applications are detected, break the loop and return the count
    if ($newPendingCount > $initialPendingCount) {
        echo json_encode(['pending_count' => $newPendingCount]);
        break;
    }

    // Wait for a second before checking again
    usleep(1000000); // 1 second

    // Timeout to prevent indefinite looping
    if (time() - $_SERVER['REQUEST_TIME'] > 30) {
        echo json_encode(['pending_count' => $initialPendingCount]);
        break;
    }
}
