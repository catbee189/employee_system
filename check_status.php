<?php
session_start();
include("./cofig.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Fetch the latest call status
$stmt = $conn->prepare("SELECT status FROM call_logs WHERE joiner_id = ? ORDER BY start_time DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($call_status);
$stmt->fetch();
$stmt->close();

echo $call_status; // Send status to JavaScript
?>
