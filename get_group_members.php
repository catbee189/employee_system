<?php
include("cofig.php"); // Correct spelling

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['group_id'])) {
    $groupId = intval($_POST['group_id']); // Ensure it's an integer

    $sql = "SELECT i.id, i.firstname FROM group_members 
            JOIN employees i ON group_members.user_id = i.id 
            WHERE group_members.group_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $groupId); // Prevent SQL injection
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = ["id" => $row["id"], "name" => $row["firstname"]]; // Ensure correct keys
    }

    echo json_encode($members);
    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}

$conn->close();
?>
