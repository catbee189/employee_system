<?php
require 'cofig.php'; // Fix typo

// Fetch call_status from the database
$group_id = $_GET['group_id'] ?? null; // Use GET or SESSION
$call_status = "";

if ($group_id) {
    $query = "SELECT call_status FROM call_logs_group WHERE group_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $stmt->bind_result($call_status);
    $stmt->fetch();
    $stmt->close();
}
?>
