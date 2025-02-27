<?php
include("cofig.php");

$caller_id = $_POST['caller_id'];
$receiver_id = $_POST['receiver_id'];

// Find the most recent ongoing call
$sql = "SELECT id, start_time FROM call_logs 
        WHERE caller_id = ? AND receiver_id = ? AND status = 'ongoing' 
        ORDER BY start_time DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $caller_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $call_id = $row['id'];
    $start_time = strtotime($row['start_time']);
    $end_time = time();
    $duration = $end_time - $start_time;

    // Update Call Status
    $update_sql = "UPDATE call_logs SET end_time = NOW(), duration = ?, status = 'completed' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $duration, $call_id);
    $update_stmt->execute();
    $update_stmt->close();

    echo "Call ended, duration: " . gmdate("H:i:s", $duration);
} else {
    echo "No active call found.";
}

$stmt->close();
$conn->close();
?>
