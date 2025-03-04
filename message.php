<?php 
include("cofig.php");
include("./layouts/header.php");
include("./layouts/sidebar.php");

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1, 1000);
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$receiver_id) {
    die("Invalid user ID.");
}

// Fetch receiver details
$query = $conn->prepare("
    SELECT firstname, lastname, profile_image FROM employees WHERE id = ? 
    UNION 
    SELECT firstname, lastname, profile_image FROM admin WHERE id = ?
");
$query->bind_param("ii", $receiver_id, $receiver_id);
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {
    $receiver_name = $row['firstname'] . " " . $row['lastname'];
    $receiver_image = !empty($row['profile_image']) ? $row['profile_image'] : 'default.jpg';
} else {
    $receiver_name = "Unknown User";
    $receiver_image = 'default.jpg';
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; }
        .chat-container { max-width: 1000px; margin: auto; margin-top: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .chat-header { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #ddd; }
        .profile-img { width: 45px; height: 45px; border-radius: 50%; margin-right: 10px; }
        .chat-box, .call-logs-box { height: 400px; overflow-y: auto; padding: 15px; background: #f8f9fa; border-radius: 5px; display: flex; flex-direction: column; }
        .message, .call-entry { max-width: 75%; padding: 10px; border-radius: 10px; margin-bottom: 10px; }
        .sent { background: #007bff; color: white; align-self: flex-end; }
        .received { background: #e9ecef; align-self: flex-start; }
        .call-entry { background: #f1c40f; color: black; align-self: center; }
        .chat-footer { padding: 10px; border-top: 1px solid #ddd; display: flex; gap: 10px; }
    </style>
</head>
<body>
<div class="container chat-container">
    <div class="chat-header">
        <button class="btn btn-link" onclick="window.history.back()">
            <i class="bi bi-arrow-left"></i>
        </button>
        <img src="uploads/<?php echo htmlspecialchars($receiver_image); ?>" class="profile-img" alt="User Image">
        <h5 class="mb-0">Chat with <?php echo htmlspecialchars($receiver_name); ?></h5>
        <div class="ms-auto">
            <a href="inserted_video.php?user_id=<?= $receiver_id ?>" class="btn btn-primary">
                <i class="bi bi-camera-video-fill"></i> Video Call
            </a>
        </div>
    </div>
<!-- Incoming Call Modal -->
<?php 
$sql = "SELECT * FROM call_logs ORDER BY start_time DESC";
$result = $conn->query($sql);

// Fetch the first row
$row = $result->fetch_assoc(); 

if ($row && $row['status'] == "in-progress") :
?>

?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var modal = new bootstrap.Modal(document.getElementById('incomingCallModal'));
            modal.show();
        });
    </script>
<?php
endif;
?>

<!-- Bootstrap Modal -->
<div class="modal fade" id="incomingCallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Incoming Video Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You have an incoming video call. Do you want to join?</p>
            </div>
            <div class="modal-footer">
                <button id="declineCallBtn" data-user-id="<?= $receiver_id ?>" class="btn btn-danger">Decline</button>
                <a href="join_accept.php?user_id=<?= $receiver_id ?>" class="btn btn-success">Join</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $("#declineCallBtn").click(function() {
        var userId = $(this).data("user-id");

        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to decline the call?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Decline",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "decline_call.php?user_id=" + userId,
                    method: "GET",
                    success: function(response) {
                        Swal.fire({
                            title: "Call Declined",
                            text: "You have successfully declined the call.",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            window.location.href = "message.php?user_id=" + userId; // Fixed issue
                        });
                    },
                    error: function() {
                        Swal.fire("Error", "Unable to decline call.", "error");
                    }
                });
            }
        });
    });
});
</script>

<!-- Include SweetAlert -->


<!-- Bootstrap Modal -->


<!-- Bootstrap JS (Ensure it's included) -->
    <!-- Row to Separate Messages and Call Logs -->
    <div class="row mt-3">
        <div class="col-md-8">
            <div class="chat-box" id="chatBox"></div>
            <p id="noMessages" class="text-center">No messages available.</p>
            <div class="chat-footer">
                <input type="text" id="message" class="form-control" placeholder="Type a message...">
                <button class="btn btn-primary" id="sendBtn"><i class="bi bi-send"></i></button>
            </div>
        </div>
        <div class="col-md-4">
            <h6 class="text-center">Call Logs</h6>
            <div class="call-logs-box" id="callLogsBox"></div>
        </div>
    </div>
</div>
<script>
    
</script>
<script>
$(document).ready(function () {
    let sender_id = <?php echo $user_id; ?>;
    let receiver_id = <?php echo $receiver_id; ?>;

    function loadMessages() {
        $.get("fetch_messages.php", { sender_id, receiver_id }, function (data) {
            let chatData = JSON.parse(data);
            let chatBox = $("#chatBox");
            chatBox.html("");

            if (chatData.length === 0) {
                $("#noMessages").show();
            } else {
                $("#noMessages").hide();
                chatData.forEach(item => {
                    let className = item.sender_id == sender_id ? "sent" : "received";
                    chatBox.append(`<div class="message ${className}">${item.message}</div>`);
                });
            }
            chatBox.scrollTop(chatBox[0].scrollHeight);
        });
    }

    function loadCallLogs() {
    $.get("fetch_calls_log.php", { sender_id, receiver_id }, function (data) {
        let callData = JSON.parse(data);
        let callLogsBox = $("#callLogsBox");
        callLogsBox.html("");

        if (callData.length === 0) {
            callLogsBox.append("<p class='text-center'>No call logs available.</p>");
        } else {
            callData.forEach(item => {
                // Skip "in-progress" calls
                if (item.status.toLowerCase() === "in-progress") {
                    return;
                }

                // Assign colors based on call status
                let colorClass = "";
                if (item.status.toLowerCase() === "declined") {
                    colorClass = "bg-danger text-white"; // Red
              
                } else if (item.status.toLowerCase() === "ended") {
                    colorClass = "bg-warning text-dark"; // Yellow
                }

                // Append call log entry
                callLogsBox.append(`
                    <div class="call-entry ${colorClass} p-2 rounded my-1">
                        ${item.start_time} - ${item.status}
                    </div>
                `);
            });
        }
    });
}

    $("#sendBtn").click(function () {
        let message = $("#message").val().trim();
        if (message === "") return;
        $.post("send_message.php", { sender_id, receiver_id, message }, function () {
            $("#message").val("");
            loadMessages();
        });
    });

    setInterval(loadMessages, 2000);
    setInterval(loadCallLogs, 5000);
});
</script>
</body>
</html>
<?php include("./layouts/footer.php"); ?>
