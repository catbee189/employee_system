<?php
session_start();
require 'cofig.php';

if (!isset($_POST['group_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit();
}

$group_id = $_POST['group_id'];

// Update all calls for this group to 'end'
$update_call = "UPDATE call_logs_group SET call_status = 'end' WHERE group_id = ?";
$stmt = $conn->prepare($update_call);
$stmt->bind_param("i", $group_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update call status."]);
}

$stmt->close();
$conn->close();
?>
