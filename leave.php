<?php
include("cofig.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $callerId = $_POST["callerId"];
    $joinerId = $_POST["joinerId"];

    $stmt = $conn->prepare("UPDATE call_logs SET status = 'end', end_time = NOW() WHERE caller_id = ? AND joiner_id = ? AND status = 'answered '");
    $stmt->bind_param("ii", $callerId, $joinerId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Call status updated."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update call status."]);
    }

    $stmt->close();
    $conn->close();
}
?>
