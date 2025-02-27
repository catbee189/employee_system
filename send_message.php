<?php
include("cofig.php"); // Ensure correct spelling

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $query = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $query->bind_param("iis", $sender_id, $receiver_id, $message);
        $query->execute();
        $query->close();
    }
}
?>
