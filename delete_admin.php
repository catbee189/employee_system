<?php
include('cofig.php'); // Include the database connection file

// Check if the 'id' is passed in the URL
if (isset($_GET['id'])) {
    // Get the ID from the URL
    $id = $_GET['id'];

    // Sanitize the ID to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $id);

    // Delete query
    $deleteQuery = "DELETE FROM `admin` WHERE id = '$id'";

    // Execute the query
    if ($conn->query($deleteQuery) === TRUE) {
        // Return a JSON response for success
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
    } else {
        // Log the error for debugging
        error_log("Error deleting record: " . $conn->error);
        
        // Return a JSON response for error
        echo json_encode(['status' => 'error', 'message' => 'Error deleting record']);
    }
} else {
    // Return a JSON response if no ID is passed
    echo json_encode(['status' => 'error', 'message' => 'No ID provided for deletion']);
}
?>
