<?php
session_start();
// Your database connection filein
include("./cofig.php");

if (!isset($_POST['user_id']) || !isset($_POST['group_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$user_id = intval($_POST['user_id']);
$group_id = intval($_POST['group_id']);

// Ensure only group admins can remove users
if ($_SESSION['role'] !== "admin" && $_SESSION['role'] !== "project manager") {
    echo json_encode(["status" => "error", "message" => "Unauthorized action"]);
    exit;
}

// Delete the user from the group
$stmt = $conn->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
$stmt->bind_param("ii", $user_id, $group_id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to remove user"]);
}
?>
