<?php 
include("./layouts/header.php");
include("./layouts/sidebar.php");
require 'cofig.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first'); window.location='login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = $_GET['group_id'] ?? null; // Retrieve the group ID from the URL

// If no group ID, redirect to groups page
if (!$group_id) {
    echo "<script>alert('Invalid Group ID'); window.location='groups.php';</script>";
    exit();
}

// Fetch group details
$group_query = "SELECT group_name FROM groups WHERE id = ?";
$group_stmt = $conn->prepare($group_query);
$group_stmt->bind_param("i", $group_id);
$group_stmt->execute();
$group_result = $group_stmt->get_result();
$group = $group_result->fetch_assoc();

if (!$group) {
    echo "<script>alert('Group not found.'); window.location='groups.php';</script>";
    exit();
}

// Fetch members of the group
$members_query = "SELECT * FROM employees u JOIN group_members gm ON u.id = gm.user_id WHERE gm.group_id = ?";
$members_stmt = $conn->prepare($members_query);
$members_stmt->bind_param("i", $group_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);

// Fetch all available users for adding to the group (excluding the current user)
$users_query = "SELECT * FROM employees WHERE id != ?";
$users_stmt = $conn->prepare($users_query);
$users_stmt->bind_param("i", $user_id); 
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Bootstrap CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    </head>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom CSS -->
    <style>
        body { background-color: #f8f9fa; }
        .chat-container { max-width: 800px; margin: auto; margin-top: 30px; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .chat-header { display: flex; align-items: center; padding: 10px 15px; border-bottom: 1px solid #ddd; background: #fff; border-radius: 10px 10px 0 0; }
        .back-btn { border: none; background: none; font-size: 22px; color: #007bff; cursor: pointer; }
        .profile-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
        .chat-box { height: 400px; overflow-y: auto; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .message { max-width: 75%; padding: 10px 15px; border-radius: 10px; margin-bottom: 10px; }
        .sent { background: #007bff; color: white; align-self: flex-end; text-align: right; }
        .received { background: #e9ecef; align-self: flex-start; text-align: left; }
        .chat-footer { padding: 10px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: white; border-radius: 0 0 10px 10px; }
        .call-buttons button { background: #007bff; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
   
      
    
        /* Video Popup */
        .video-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
}

.video-container {
    background: #222;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

#videoGrid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
}

video {
    width: 200px;
    border-radius: 10px;
    background: black;
}

.controls {
    margin-top: 10px;
    display: flex;
    justify-content: center;
    gap: 10px;
}

button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}
        #videoPopup, #incomingCallPopup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border: 2px solid black;
            z-index: 1000;
        }
        video {
            width: 200px;
            height: 150px;
            margin: 5px;
            border: 1px solid black;
        }

   </style>
</head>
<body>

    <div class="container chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <!-- Back Button -->
            <button class="back-btn" onclick="goBack()"> <i class="bi bi-arrow-left"></i> </button>
            <!-- User Profile -->
            <h2 class="text-center mb-0">Group: <?= htmlspecialchars($group['group_name']); ?></h2>
            <!-- View Members Button -->
            <?php if ($_SESSION['role'] === "admin" || $_SESSION['role'] === "project manager") : ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">View Members</button>
<?php endif; ?>

            <ul class="dropdown-menu">
                <li><h6 class="dropdown-header">Current Members</h6></li>
                <?php foreach ($members as $user): ?>
                    <li class="list-group-item d-flex align-items-center mb-2" data-id="<?= htmlspecialchars($user['id']) ?>">
    <img src="<?= !empty($user['profile_image']) ? 'uploads/' . htmlspecialchars($user['profile_image']) : 'uploads/default.png'; ?>" class="user-avatar rounded-circle me-3" width="50" height="50">
    <span class="user-status <?= htmlspecialchars($user['status'] ?? 'offline') ?>"></span>
    <div class="me-auto">
        <strong><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></strong><br>
        <small class="text-muted"><?= htmlspecialchars($user['role'] ?? '') ?></small>
    </div>
    <!-- Remove Button -->
    <a href="#" class="btn btn-danger remove-user" data-id="<?= htmlspecialchars($user['id']) ?>">-</a>
</li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addMemberModal">Add Member</button>
                </li>
            </ul>
          
           <!-- Call Buttons -->
           <div id="groupContainer"></div>
           <button id="startGroupCall">Start Group Call</button>
    <div id="groupContainer"></div>

    <!-- Incoming Call Popup -->
    <div id="incomingCallPopup">
        <h3>Incoming Call</h3>
        <button id="joinCall">Join Call</button>
        <button id="declineCall">Decline</button>
    </div>

    <!-- Video Call Popup -->
    <div id="videoPopup">
        <h3>Video Call</h3>
        <div id="videoGrid"></div>
        <button id="endCall">End Call</button>
    </div>
   </div>

        <!-- Modal for Adding Members -->
        <div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="addMemberModalLabel">Add Member to Group</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="add_member.php?group_id=<?= $group_id ?>">  
                            <div class="mb-3">
                                <label for="new_member" class="form-label">Select Members</label>
                                <?php foreach ($users as $user): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="new_members[]" value="<?= $user['id']; ?>" id="user<?= $user['id']; ?>">
                                        <label class="form-check-label" for="user<?= $user['id']; ?>">
                                            <div class="list-group-item d-flex align-items-center mb-2">
                                                <img src="<?= !empty($user['profile_image']) ? 'uploads/' . htmlspecialchars($user['profile_image']) : 'uploads/default.png'; ?>" class="user-avatar rounded-circle me-3" width="50" height="50">
                                                <span class="user-status <?= htmlspecialchars($user['status'] ?? 'offline') ?>"></span>
                                                <div class="me-auto">
                                                    <strong><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($user['role'] ?? '') ?></small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Member</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call Buttons -->
      

        <!-- Chat Box -->
        <div class="chat-box d-flex flex-column" id="chatBox"></div>
        <p id="noMessages" style="text-align:center;">No messages available. Once you send a message, they will appear here.</p>

    <div class="chat-footer d-flex mt-2">
        <input type="hidden" id="group_id" value="<?= $group_id ?>">
        <input type="text" id="message" class="form-control me-2" placeholder="Type a message..." autocomplete="off">
        <button class="btn btn-primary" id="sendBtn"><i class="bi bi-send"></i> Send</button>
    </div>

   

<script>
$(document).ready(function () {
    let sender_id = <?= $_SESSION['user_id']; ?>;
    let group_id = $("#group_id").val();

    function loadMessages() {
        $.get("view_messages.php", { group_id: group_id }, function (data) {
            try {
                console.log("Raw response:", data); // Debugging
                
                if (data.error) {
                    console.error("Error:", data.error);
                    return;
                }

                let messages = JSON.parse(JSON.stringify(data)); // Fix JSON issues
                let chatBox = $("#chatBox");
                let noMessageText = $("#noMessages");

                chatBox.html(""); // Clear previous messages

                if (messages.length === 0) {
                    noMessageText.show();
                } else {
                    noMessageText.hide();
                    messages.forEach(msg => {
                        let className = msg.sender_id == sender_id ? "sent" : "received";
                        let messageHTML = `<div class="message ${className}">
                            <strong>${msg.sender_name}:</strong> <small>${msg.role}:</small> ${msg.message}
                        </div>`;
                        chatBox.append(messageHTML);
                    });

                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }
            } catch (error) {
                console.error("Error parsing messages:", error, "Raw response:", data);
            }
        });
    }

    $("#sendBtn").click(function () {
        let message = $("#message").val().trim();
        if (message === "") {
            alert("Message cannot be empty!");
            return;
        }

        $.post("send_group.php", { group_id: group_id, message: message }, function (response) {
            try {
          console.log(response); // Debugging: Check response from PHP
                let result = JSON.parse(response);
                if (result.status === "success") {
                    $("#message").val(""); // Clear input
                    loadMessages(); // Reload chat immediately
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error("Error parsing response:", error);
                loadMessages();
            }
        });
    });

    setInterval(loadMessages, 2000); // Auto refresh every 2 seconds

    loadMessages(); // Initial load when page loads
});


$(document).ready(function () {
    $(".remove-user").click(function (e) {
        e.preventDefault();
        
        let userId = $(this).data("id");
        let groupId = $("#group_id").val(); // Assuming group ID is stored in a hidden input

        if (!confirm("Are you sure you want to remove this user from the group?")) {
            return;
        }

        $.post("remove_user.php", { user_id: userId, group_id: groupId }, function (response) {
            try {
                console.log(response); // Debugging

                let result = JSON.parse(response);
                if (result.status === "success") {
                    alert("User removed successfully!");
                    $(`li[data-id='${userId}']`).remove(); // Remove user from UI
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error("Error parsing response:", error);
            }
        });
    });
});


</script>
  <script>
     const socket = io("http://localhost:3000");
const videoGrid = document.getElementById("videoGrid");
let localStream;
let peerConnections = {};
let groupId = 1; // Change as needed
let userId = "user123"; // Should be dynamically set from login

// Check if the user is a member before showing join option
socket.emit("requestJoin", groupId, userId);

socket.on("joinRequest", (canJoin) => {
    if (canJoin) {
        document.getElementById("incomingCallPopup").style.display = "block";
    } else {
        alert("You are not a member of this group.");
    }
});

// Start Group Call
document.getElementById("startGroupCall").addEventListener("click", () => {
    document.getElementById("videoPopup").style.display = "block";
    startVideoStream();
    socket.emit("joinCall", groupId, userId);
});

// Accept Call
document.getElementById("joinCall").addEventListener("click", () => {
    document.getElementById("incomingCallPopup").style.display = "none";
    document.getElementById("videoPopup").style.display = "block";
    startVideoStream();
    socket.emit("joinCall", groupId, userId);
});

// Decline Call
document.getElementById("declineCall").addEventListener("click", () => {
    document.getElementById("incomingCallPopup").style.display = "none";
    socket.emit("declineCall", groupId, userId);
});

// End Call
document.getElementById("endCall").addEventListener("click", () => {
    document.getElementById("videoPopup").style.display = "none";
    stopVideoStream();
    socket.emit("endCall", groupId, userId);
});

// Get User Media
function startVideoStream() {
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            localStream = stream;
            const videoElement = document.createElement("video");
            videoElement.srcObject = stream;
            videoElement.autoplay = true;
            videoElement.muted = true;
            videoGrid.appendChild(videoElement);
        })
        .catch(error => console.error("Error accessing camera:", error));
}

// Stop Video Stream
function stopVideoStream() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    videoGrid.innerHTML = "";
}

// Handle New Users Joining
socket.on("userConnected", (userId) => {
    console.log("User joined:", userId);
    const videoElement = document.createElement("video");
    videoElement.autoplay = true;
    videoGrid.appendChild(videoElement);
});

// Handle Call Ended
socket.on("callEnded", () => {
    document.getElementById("videoPopup").style.display = "none";
    stopVideoStream();
});

    </script>
<script src="./server.js"></script>
    <!-- Bootstrap JS and other required scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>

<?php include("./layouts/footer.php"); ?>
