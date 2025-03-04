<?php
session_start();
include("./cofig.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$receiver_id) {
    die("Invalid user ID.");
}

// Insert video call request into the database
$stmt = $conn->prepare("INSERT INTO call_logs (caller_id, joiner_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $user_id, $receiver_id);

if ($stmt->execute()) {
    // Redirect to the single video page after successful insertion
    header("Location: singe_call.php?user_id=" . $receiver_id);
    exit;
} else {
    die("Error inserting video call request: " . $stmt->error);
}


?>
