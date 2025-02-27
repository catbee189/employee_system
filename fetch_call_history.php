<?php
include 'cofig.php';


$receiver_id = $_GET['receiver_id'];

$sql = "SELECT * FROM call_logs
        WHERE (sender_id='$sender_id' AND receiver_id='$receiver_id') 
        OR (sender_id='$receiver_id' AND receiver_id='$sender_id') 
        ORDER BY timestamp ASC";

$result = mysqli_query($conn, $sql);
$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row; 
}

echo json_encode($messages);
?>
