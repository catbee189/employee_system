<?php
session_start();
require 'cofig.php';

if (!isset($_POST['group_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit();
}

$group_id = $_POST['group_id'];
$user_id = $_SESSION['user_id'];

// Start transaction to ensure atomicity
$conn->begin_transaction();

try {
    // Update all previous calls to 'in-progress' for the same group_id
    $update_calls = "UPDATE call_logs_group SET call_status = 'in-progress' WHERE group_id = ?";
    $stmt = $conn->prepare($update_calls);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $stmt->close();

    // Insert a new call entry
    $insert_call = "INSERT INTO call_logs_group (group_id, user_id, call_status, start_time) VALUES (?, ?, 'in-progress', NOW())";
    $stmt = $conn->prepare($insert_call);
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to update call status."]);
}

$conn->close();
?>
