<?php
include './cofig.php'; // Ensure this file exists and is correct

header('Content-Type: application/json'); // Ensure correct response type

$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

$sql = "SELECT gm.message, gm.sender_id ,  COALESCE( a.role,e.role) as role,
       COALESCE( a.firstname,e.firstname) AS sender_name
FROM group_message gm
LEFT JOIN admin a ON gm.sender_id = a.id
LEFT JOIN employees e ON gm.sender_id = e.id
WHERE gm.group_id = ? 
ORDER BY gm.created_at ASC;";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "SQL error: " . $conn->error]));
}

$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

if (empty($messages)) {
    die(json_encode(["error" => "No messages found for group_id: $group_id"]));
}

// Ensure output is valid JSON
echo json_encode($messages);
?>
