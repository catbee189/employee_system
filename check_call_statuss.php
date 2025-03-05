<?php
include("./cofig.php");

$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    echo json_encode(["error" => "Invalid Group ID"]);
    exit();
}

$sql = "SELECT call_status FROM call_logs_group WHERE group_id = ? ORDER BY start_time DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$call_status = $row['call_status'] ?? "active";

$stmt->close();
$conn->close();

echo json_encode(["call_status" => $call_status]);
?>
