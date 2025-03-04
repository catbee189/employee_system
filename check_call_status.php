<?php
include("./cofig.php");

$sql = "SELECT * FROM call_logs ORDER BY start_time DESC LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$response = ["status" => $row ? $row['status'] : "unknown"];
echo json_encode($response);
?>
