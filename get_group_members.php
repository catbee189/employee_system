<?php
include 'cofig.php'; // Database connection

$group_id = $_GET['group_id'];

$sql = "SELECT e.id, u.email FROM employees e
        JOIN group_members gm ON u.id = gm.user_id
        WHERE gm.group_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

echo json_encode($members);
?>
