<?php
include("cofig.php");

$caller_id = $_POST['caller_id'];
$receiver_id = $_POST['receiver_id'];
$call_type = $_POST['call_type'];

$sql = "INSERT INTO call_logs (caller_id, receiver_id, call_type) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $caller_id, $receiver_id, $call_type);

if ($stmt->execute()) {
    echo "Call logged successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
