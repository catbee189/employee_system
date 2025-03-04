<?php
session_start();
include("./cofig.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Check for required data
if (!isset($data['callerId']) || !isset($data['joinerId'])) {
    echo json_encode(["error" => "Missing data."]);
    exit;
}

$caller_id = $data['callerId'];
$joiner_id = $data['joinerId'];
$end_time = date('Y-m-d H:i:s'); // Get the current time
$status = "end"; // Set the status to "end"

// Update the call status to 'end'
$stmt = $conn->prepare("UPDATE `call_logs` SET status = ?, end_time = ? WHERE caller_id = ? AND joiner_id = ?");
$stmt->bind_param("ssii", $status, $end_time, $caller_id, $joiner_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Call status updated successfully."]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
?>
