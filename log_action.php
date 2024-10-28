<?php
function logAction($pdo, $user_id, $activity_description) {
    $query = $pdo->prepare("INSERT INTO activity_logs (user_id, activity_description, timestamp) VALUES (?, ?, NOW())");
    $query->execute([$user_id, $activity_description]);
}
