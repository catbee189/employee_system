<?php
include("cofig.php");

$groupId = $_POST['group_id'];
$userId = $_POST['user_id'];
$action = $_POST['action'];
$timestamp = date("Y-m-d H:i:s");

if ($action == "start") {
    $sql = "INSERT INTO call_logs_group (group_id, user_id, start_time) 
            VALUES ('$groupId', '$userId', '$timestamp')";
} elseif ($action == "end") {
    $sql = "UPDATE call_logs_group SET end_time='$timestamp' 
            WHERE group_id='$groupId' AND user_id='$userId' AND end_time IS NULL";
} elseif ($action == "decline") {
    $sql = "INSERT INTO call_logs_group (group_id, user_id, declined_at) 
            VALUES ('$groupId', '$userId', '$timestamp')";
}

if ($conn->query($sql) === TRUE) {
    echo "Success";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
