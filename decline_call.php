<?php
session_start();
include("./cofig.php"); // Ensure correct file name

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$user_id = $_SESSION['user_id'];

// Check if user_id is passed in URL
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("Error: Invalid user_id.");
}

$receiver_id = intval($_GET['user_id']);

include("./config.php"); // Ensure correct file name

// Update the call status to 'declined'
$stmt = $conn->prepare("UPDATE call_logs SET status = 'declined' WHERE caller_id = ? AND joiner_id = ?");
$stmt->bind_param("ii", $receiver_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            title: 'Call Declined',
            text: 'You have successfully declined the call.',
            icon: 'warning',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'message.php?$user_id'; // Redirect after alert
            }
        });
    </script>";
    exit();
} else {
    echo "Error updating call status: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
