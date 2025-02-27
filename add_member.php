<?php
require 'cofig.php';
// Ensure the group_id is passed in the URL or POST request
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    die("Group ID is missing.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_GET['group_id'];  // Assuming group_id is passed via URL
    if (isset($_POST['new_members']) && is_array($_POST['new_members'])) {
        $new_members = $_POST['new_members'];

        // Loop through selected members and insert them into the group
        foreach ($new_members as $member_id) {
            // Check if the member is already in the group to prevent duplicates
            $check_query = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("ii", $group_id, $member_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                // Insert new member into the group
                $insert_query = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("ii", $group_id, $member_id);
                $insert_stmt->execute();
            }
        }

        // Redirect back to the group page
        echo "<script>alert('Members added successfully'); window.location='chat.php?group_id=$group_id';</script>";
    } else {
        echo "<script>alert('No members selected'); window.location='chat.php?group_id=$group_id';</script>";
    }
}
?>
