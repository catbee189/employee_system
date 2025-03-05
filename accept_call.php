<?php
require 'cofig.php';

$group_id = $_GET['group_id'] ?? null;

if ($group_id) {
    // Update the call status to 'answered'
    $update_query = "UPDATE call_logs_group SET call_status = 'answered' WHERE group_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $group_id);
    
    if ($stmt->execute()) {
        // Redirect to the group call page after updating
        header("Location: group_call.php?group_id=" . $group_id);
        exit();
    } else {
        echo "Failed to update call status.";
    }
} else {
    echo "Invalid group ID.";
}
?>
