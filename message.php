    <?php 
    include("cofig.php"); // Ensure correct spelling
    include("./layouts/header.php");
    include("./layouts/sidebar.php");

    // Dummy login system for testing
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = rand(1, 1000); 
    }

    $user_id = $_SESSION['user_id'];
    $receiver_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

    if (!$receiver_id) {
        die("Invalid user ID.");
    }

    // Fetch receiver's name and image from `employee` and `admin` tables
    $query = $conn->prepare("
        SELECT firstname, lastname, profile_image, 'employee' AS user_type FROM employees WHERE id = ? 
        UNION 
        SELECT firstname, lastname, profile_image, 'admin' AS user_type FROM admin WHERE id = ?
    ");
    $query->bind_param("ii", $receiver_id, $receiver_id);
    $query->execute();
    $result = $query->get_result();

    if ($row = $result->fetch_assoc()) {
        $receiver_name = $row['firstname'] . " " . $row['lastname'];
        $receiver_image = !empty($row['profile_image']) ? $row['profile_image'] : 'default.jpg'; // Default image
    } else {
        $receiver_name = "Unknown User";
        $receiver_image = 'default.jpg';
    }
    $query->close();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Messenger</title>

        <!-- Bootstrap CSS & Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        
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
        </style>
    </head>
    <body>

        <div class="container chat-container">
            <!-- Chat Header -->
            <div class="chat-header">
    <!-- Back Button -->
    <button class="back-btn" onclick="goBack()">
        <i class="bi bi-arrow-left"></i>
    </button>

    <!-- User Profile -->
    <img src="uploads/<?php echo htmlspecialchars($receiver_image); ?>" class="profile-img" alt="User Image">
    <h5 class="mb-0">Chat with <?php echo htmlspecialchars($receiver_name); ?></h5>

    <!-- Call Buttons -->
    <div class="call-buttons"style="display:flex;justify-content:space_between;">
        <button id="startCall">
            <i class="bi bi-telephone-fill"></i> Call
        </button>
       

        <button id="startVideoCall" >
            <i class="bi bi-camera-video-fill"></i> Video Call
        </button>
    </div>
</div>

            <!-- Chat Box -->
            <div class="chat-box d-flex flex-column" id="chatBox"></div>
            <p id="noMessages" style="text-align:center;">No messages available. Once you send a message, they will appear here.</p>
            <!-- Chat Footer / Input -->
            <div class="chat-footer">
                <input type="text" id="message" class="form-control" placeholder="Type a message..." autocomplete="off">
                <button class="btn btn-primary" id="sendBtn"><i class="bi bi-send"></i></button>
            </div>
        </div>
        <div id="videoCallModal" class="video-modal">
    <div class="video-container">
        <video id="localVideo" autoplay playsinline></video>
        <div class="controls">
            <button id="muteAudio">Mute</button>
            <button id="closeCamera">Close Camera</button>
            <button id="endCall" class="end-call">End Call</button>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
.video-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 400px;
    background: rgba(0, 0, 0, 0.8);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}
.video-container {
    position: relative;
}
#localVideo {
    width: 100%;
    border-radius: 10px;
}
.controls {
    margin-top: 10px;
}
.controls button {
    margin: 5px;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.end-call {
    background: red;
    color: white;
}
</style>
<style>
.chat-header {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.back-btn {
    background: none;
    border: none;
    font-size: 20px;
    margin-right: 10px;
    cursor: pointer;
}

.profile-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}

.call-buttons {
    margin-left: auto; /* Push buttons to the left */
}

.call-buttons button {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.call-buttons button i {
    margin-right: 5px;
}

.call-buttons button:hover {
    background: #0056b3;
}
</style>

<!-- JavaScript -->
<script>
let localStream;

document.getElementById("startVideoCall").addEventListener("click", async () => {
    let modal = document.getElementById("videoCallModal");
    modal.style.display = "block";

    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        document.getElementById("localVideo").srcObject = localStream;
    } catch (error) {
        alert("Error accessing camera: " + error);
    }
});

// Mute / Unmute Audio
document.getElementById("muteAudio").addEventListener("click", () => {
    let audioTracks = localStream.getAudioTracks();
    if (audioTracks.length > 0) {
        audioTracks[0].enabled = !audioTracks[0].enabled;
        document.getElementById("muteAudio").textContent = audioTracks[0].enabled ? "Mute" : "Unmute";
    }
});

// Close Camera
document.getElementById("closeCamera").addEventListener("click", () => {
    let videoTracks = localStream.getVideoTracks();
    if (videoTracks.length > 0) {
        videoTracks[0].enabled = !videoTracks[0].enabled;
        document.getElementById("closeCamera").textContent = videoTracks[0].enabled ? "Close Camera" : "Open Camera";
    }
});

// End Call
document.getElementById("endCall").addEventListener("click", () => {
    let modal = document.getElementById("videoCallModal");
    modal.style.display = "none";

    if (localStream) {
        localStream.getTracks().forEach(track => track.stop()); // Stop all tracks
    }
});
</script>
        <script>
         $(document).ready(function () {
    let sender_id = <?php echo $user_id; ?>;
    let receiver_id = <?php echo $receiver_id; ?>;

    function loadMessages() {
        $.get("fetch_messages.php", { sender_id, receiver_id }, function (data) {
            let messages = JSON.parse(data);
            let chatBox = $("#chatBox");
            let noMessageText = $("#noMessages");

            chatBox.html(""); // Clear previous messages

            if (messages.length === 0) {
                noMessageText.show();
            } else {
                noMessageText.hide();
                messages.forEach(msg => {
                    let className = msg.sender_id == sender_id ? "sent" : "received";
                    let messageHTML = `<div class="message ${className}">${msg.message}</div>`;
                    chatBox.append(messageHTML);
                });

                chatBox.scrollTop(chatBox[0].scrollHeight);
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

    setInterval(loadMessages, 2000); // Auto refresh every 2 seconds
});

            function goBack() {
                window.location.href = "admin_communication.php"; // Change this as needed
            }
        </script>

    </body>
    </html>

    <?php include("./layouts/footer.php"); ?>
