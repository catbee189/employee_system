<?php
include("cofig.php");

$sender_id = $_GET['sender_id'] ?? null;
$receiver_id = $_GET['receiver_id'] ?? null;

if (!$sender_id || !$receiver_id) {
    echo json_encode([]);
    exit;
}

$query = $conn->prepare("SELECT start_time, status FROM call_logs WHERE (caller_id = ? AND joiner_id = ?) OR (caller_id = ? AND joiner_id = ?) ORDER BY start_time DESC");
$query->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$query->execute();
$result = $query->get_result();

$calls = [];
while ($row = $result->fetch_assoc()) {
    $calls[] = $row;
}

echo json_encode($calls);
?>
