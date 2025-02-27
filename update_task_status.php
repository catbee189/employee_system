<?php
// Include the database configuration file
include('config.php');

// Check if the request is a POST request and contains necessary data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pe_id']) && isset($_POST['task_status'])) {
    $peId = intval($_POST['pe_id']); // Sanitize input
    $taskStatus = $_POST['task_status'];

    // Prepare the SQL query to update task status
    $query = $conn->prepare("UPDATE project_employees SET task_status = ? WHERE id = ?");
    $query->bind_param("si", $taskStatus, $peId);

    // Execute the query and check for success
    if ($query->execute()) {
        echo "Task status updated successfully.";
    } else {
        echo "Error updating task status.";
    }

    // Close the connection
    $query->close();
    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
?>
