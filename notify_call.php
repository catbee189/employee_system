<?php
include("./cofig.php");

if (!isset($_GET['group_id'])) {
    die(json_encode(["error" => "No group ID provided"]));
}

$group_id = intval($_GET['group_id']);

// Update call status
$sql_update = "UPDATE call_logs_group SET status='ringing' WHERE group_id = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$stmt->close();

// Select group members
$sql_select = "SELECT users.firstname, s.group_id  
               FROM group_members s
               JOIN employees users ON s.user_id = users.id 
               WHERE s.group_id = ?";

$stmt = $conn->prepare($sql_select);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row['firstname'];
}
$stmt->close();

// Send JSON response
echo json_encode(["success" => true, "members" => $members]);
?>
