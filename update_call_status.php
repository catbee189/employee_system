<?php
session_start();
include("./cofig.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if (!$receiver_id) {
    die("Invalid request.");
}

// Update the call status to "declined"
$stmt = $conn->prepare("UPDATE call_logs SET status = 'in-progress' WHERE caller_id = ? AND joiner_id = ?");
$stmt->bind_param("ii", $user_id, $receiver_id);

if ($stmt->execute()) {
    echo "success"; // This is sent back to JavaScript
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
