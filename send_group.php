<?php
session_start();
 include("./cofig.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_POST['group_id'];
    $message = trim($_POST['message']);
    $sender_id = $_SESSION['user_id']; // Get sender's ID from session

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO group_message (group_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $group_id, $sender_id, $message);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send message."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
