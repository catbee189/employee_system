<?php
session_start();
include("cofig.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID."]);
    exit();
}

$receiver_id = intval($_GET['user_id']);

// Update the call status to 'declined'
$stmt = $conn->prepare("UPDATE call_logs SET status = 'declined' WHERE caller_id = ? AND joiner_id = ?");
$stmt->bind_param("ii", $receiver_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update call status."]);
}

$stmt->close();
$conn->close();
?>
