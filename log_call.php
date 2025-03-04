<?php
include("cofig.php");

$groupId = $_POST['group_id'];
$userId = $_POST['user_id'];
$action = $_POST['action'];
$timestamp = date("Y-m-d H:i:s");

if ($action == "start") {
    $sql = "INSERT INTO call_logs_group (group_id, user_id, start_time) VALUES ('$groupId', '$userId', '$timestamp')";
} elseif ($action == "end") {
    $sql = "UPDATE call_logs_group SET end_time='$timestamp' WHERE group_id='$groupId' AND user_id='$userId' AND end_time IS NULL";
} elseif ($action == "decline") {
    $sql = "UPDATE call_logs_group SET declined_at='$timestamp' WHERE group_id='$groupId' AND user_id='$userId'";
}

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => $conn->error]);
}

$conn->close();
?>
