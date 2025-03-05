<?php
include("./cofig.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sql = "UPDATE call_logs SET status='ended', end_time=NOW() WHERE status='answered'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
?>
