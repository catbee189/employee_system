<?php
include "./cofig.php"; // Your database connection file

// Query to check if any group call is in-progress
$sql = "SELECT group_id FROM call_logs_group WHERE  call_status='in-progress' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["call_status" => "in-progress", "group_id" => $row['group_id']]);
} else {
    echo json_encode(["call_status" => "idle"]);
}
?>
