<?php
require 'cofig.php'; // Fix typo: 'cofig.php' → 'config.php'

header("Content-Type: application/json"); // Ensure JSON response

$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    echo json_encode(["status" => "error", "message" => "Invalid group ID."]);
    exit();
}

// Update the call status to 'declined'
$update_query = "UPDATE call_logs_group SET call_status = 'declined' WHERE group_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $group_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Call declined successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "No matching record found."]);
}

$stmt->close();
$conn->close();
exit();

?>
