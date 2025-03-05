<?php 
include("./cofig.php"); // Fix the typo: "cofig.php" → "config.php"



$group_id = $_GET['group_id'] ?? null; // Retrieve the group ID from the URL

// If no group ID, redirect to groups page
if (!$group_id) {
    echo "<script>alert('Invalid Group ID'); window.location='groups.php';</script>";
    exit();
}

// Fetch call status for the specific group
$sql = "SELECT call_status FROM call_logs_group WHERE group_id = ? ORDER BY start_time DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$is_declined = $row && $row['call_status'] == "declined";

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>Group Video Call</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background: black;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .video-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            padding: 20px;
        }
        video {
            width: 250px;
            height: auto;
            border: 2px solid white;
            border-radius: 10px;
        }
        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        button {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        #toggleCamera { background: #ff4d4d; color: white; }
        #toggleMic { background: #ffcc00; color: black; }
        #endCall { background: #333; color: white; }
        button:hover {
            opacity: 0.8;
        }
        .modal-body p {
            color: blue;
            font-weight: bold;
        }
    </style>
    </style>
</head>
<body>
    <h1>Group Video Call</h1>
    <div class="video-grid" id="videoGrid">
        <video id="localVideo" autoplay playsinline muted></video>
    </div>

    <div class="controls">
        <button id="toggleCamera">Camera Off</button>
        <button id="toggleMic">Mute</button>
        <button id="endCall">End Call</button>
    </div>
   <!-- Bootstrap Modal (Invite Declined) -->
   <<!-- Bootstrap Modal (Invite Declined) -->
<div class="modal fade" id="inviteDeclinedModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Call Invite Declined</h5>
            </div>
            <div class="modal-body">
                <p>Your invite has been declined.</p>
            </div>
            <div class="modal-footer">
                <a href="chat.php?group_id=<?= $group_id ?>" class="btn btn-success" id="confirmExit">OK</a>
            </div>
        </div>
    </div>
</div>

<script>
    function checkCallStatus() {
        $.ajax({
            url: "check_call_statuss.php",
            type: "GET",
            data: { group_id: "<?= $group_id ?>" },
            success: function(response) {
                let data = JSON.parse(response);
                if (data.call_status === "declined") {
                    let modal = new bootstrap.Modal(document.getElementById('inviteDeclinedModal'));
                    modal.show();

                    // Prevent closing the modal by clicking outside
                    $('#inviteDeclinedModal').on('hide.bs.modal', function (e) {
                        if (!$('#confirmExit').is(':focus')) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            return false;
                        }
                    });
                }
            },
            error: function(error) {
                console.error("Error checking call status:", error);
            }
        });
    }

    // Check the call status every 5 seconds
    setInterval(checkCallStatus, 5000);
</script>


    <!-- Bootstrap Modal -->
    
    <script>
        const ws = new WebSocket("ws://localhost:3001");
        let userId = "user_" + Math.floor(Math.random() * 10000);
        let localStream;
        let peerConnections = {};
        let isMicEnabled = true;
        let isCameraEnabled = true;

        async function startCall() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                document.getElementById("localVideo").srcObject = localStream;
                ws.send(JSON.stringify({ type: "register", userId }));
            } catch (error) {
                console.error("Error accessing media devices.", error);
            }
        }

        function createPeerConnection(remoteUserId) {
            const peerConnection = new RTCPeerConnection({
                iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
            });

            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

            peerConnection.onicecandidate = event => {
                if (event.candidate) {
                    ws.send(JSON.stringify({
                        type: "candidate",
                        target: remoteUserId,
                        candidate: event.candidate,
                        from: userId
                    }));
                }
            };

            peerConnection.ontrack = event => {
                let remoteVideo = document.getElementById("video_" + remoteUserId);
                if (!remoteVideo) {
                    remoteVideo = document.createElement("video");
                    remoteVideo.id = "video_" + remoteUserId;
                    remoteVideo.autoplay = true;
                    remoteVideo.playsInline = true;
                    document.getElementById("videoGrid").appendChild(remoteVideo);
                }
                remoteVideo.srcObject = event.streams[0];
            };

            return peerConnection;
        }

        ws.onmessage = async (message) => {
            const data = JSON.parse(message.data);

            if (data.type === "user_joined" && data.userId !== userId) {
                peerConnections[data.userId] = createPeerConnection(data.userId);
                const offer = await peerConnections[data.userId].createOffer();
                await peerConnections[data.userId].setLocalDescription(offer);
                ws.send(JSON.stringify({ type: "offer", target: data.userId, offer, from: userId }));
            } else if (data.type === "offer") {
                peerConnections[data.from] = createPeerConnection(data.from);
                await peerConnections[data.from].setRemoteDescription(new RTCSessionDescription(data.offer));
                const answer = await peerConnections[data.from].createAnswer();
                await peerConnections[data.from].setLocalDescription(answer);
                ws.send(JSON.stringify({ type: "answer", target: data.from, answer, from: userId }));
            } else if (data.type === "answer") {
                await peerConnections[data.from].setRemoteDescription(new RTCSessionDescription(data.answer));
            } else if (data.type === "candidate") {
                await peerConnections[data.from].addIceCandidate(new RTCIceCandidate(data.candidate));
            } else if (data.type === "user_left") {
                if (peerConnections[data.userId]) {
                    peerConnections[data.userId].close();
                    delete peerConnections[data.userId];
                    let videoElement = document.getElementById("video_" + data.userId);
                    if (videoElement) videoElement.remove();
                }
            }
        };

        document.getElementById("endCall").addEventListener("click", () => {
    let groupId = "<?= $group_id ?>"; // Get the group ID

    fetch("end_call_group.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "group_id=" + groupId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            window.location.href = "chat.php?group_id=" + groupId; // Redirect after updating
        } else {
            alert("Failed to end the call.");
        }
    })
    .catch(error => console.error("Error ending call:", error));
});


        // Toggle Camera
        document.getElementById("toggleCamera").addEventListener("click", () => {
            isCameraEnabled = !isCameraEnabled;
            localStream.getVideoTracks()[0].enabled = isCameraEnabled;
            document.getElementById("toggleCamera").innerText = isCameraEnabled ? "Camera Off" : "Camera On";
            document.getElementById("toggleCamera").style.background = isCameraEnabled ? "#ff4d4d" : "#00cc66";
        });

        // Toggle Mic
        document.getElementById("toggleMic").addEventListener("click", () => {
            isMicEnabled = !isMicEnabled;
            localStream.getAudioTracks()[0].enabled = isMicEnabled;
            document.getElementById("toggleMic").innerText = isMicEnabled ? "Mute" : "Unmute";
            document.getElementById("toggleMic").style.background = isMicEnabled ? "#ffcc00" : "#0066ff";
        });

        startCall();
    </script>
</body>
</html>
