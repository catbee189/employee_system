<?php
session_start();
include("./cofig.php"); // Fixed typo (was "cofig.php")

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null; // Ensure it's an integer

if (!$receiver_id) {
    die("Invalid user ID.");
}

// Prepare the SQL query
$stmt = $conn->prepare("UPDATE call_logs SET status = 'answered' WHERE caller_id = ? AND joiner_id = ?");
$stmt->bind_param("ii", $user_id, $receiver_id);

if ($stmt->execute()) {
    $stmt->close();

    // Redirect to join_accept.php (or change it to join_call.php if needed)
    header("Location: join_call.php?user_id=$receiver_id");
    exit();
} else {
    echo "Error updating call status: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
