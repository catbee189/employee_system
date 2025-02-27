<?php
session_start();
require 'cofig.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$group_name = $_POST['group_name'];
$admin_id = $_SESSION['user_id'];

if (empty($group_name)) {
    echo json_encode(["status" => "error", "message" => "Group name is required"]);
    exit();
}

// Insert group into database
$stmt = $conn->prepare("INSERT INTO groups (group_name, created_by) VALUES (?, ?)");
$stmt->bind_param("si", $group_name, $admin_id);
if ($stmt->execute()) {
    $group_id = $stmt->insert_id;
    echo json_encode(["status" => "success", "group_id" => $group_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create group"]);
}
?>
