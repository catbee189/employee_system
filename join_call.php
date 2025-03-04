<?php
session_start();
include("./cofig.php");

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1, 1000); // Replace with actual login logic
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$receiver_id) {
    die("Invalid user ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Joiner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            text-align: center;
            background: black;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        h1 {
            margin-top: 20px;
            font-size: 1.5em;
            color: white;
        }
        video {
            border: 2px solid white;
            background: black;
            border-radius: 10px;
        }
        #localVideo {
            width: 160px;
            height: auto;
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 10;
            border-radius: 10px;
        }
        #remoteVideo {
            position: absolute;
            top: 10;
            left: 50%;
            transform: translateX(-50%);
            width: 30vw;
            height: 40vh;
            object-fit: cover;
            z-index: 1;
        }
        .controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            z-index: 20;
        }
        button {
            padding: 12px 25px;
            background: #444;
            color: white;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #666;
        }
        #endCall {
            background: #e74c3c;
        }
        #endCall:hover {
            background: #c0392b;
        }
        .modal-body p {
        color: blue;
        font-weight: bold;
    }
    </style>
</head>
<body>
    <h1>Joiner</h1>
    <video id="localVideo" autoplay playsinline muted></video>
    <video id="remoteVideo" autoplay playsinline></video>
    <div class="controls">
        <button id="toggleCamera">Turn Camera Off</button>
        <button id="toggleMic">Mute</button>
        <button id="endCall">End Call</button>
    </div>

    <?php 
    $sql = "SELECT * FROM call_logs ORDER BY start_time DESC";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc(); 
    if ($row && $row['status'] == "end") :
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var modal = new bootstrap.Modal(document.getElementById('incomingCallModal'));
            modal.show();
        });
    </script>
    <?php endif; ?>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="incomingCallModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Incoming Video Call</h5>
                <!-- Remove close button -->
            </div>
            <div class="modal-body">
                <p>Call has ended.</p>
            </div>
            <div class="modal-footer">
                <a href="message.php?user_id=<?= $receiver_id ?>" class="btn btn-success">OK</a>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ws = new WebSocket("ws://localhost:3001");
        let peerConnection = null;
        let localStream;
        let userId = "joiner_" + Math.floor(Math.random() * 10000);
        let targetId = "caller";
        const localVideoElem = document.getElementById("localVideo");
        const remoteVideoElem = document.getElementById("remoteVideo");
        let micEnabled = true;
        let cameraEnabled = true;

        ws.onopen = () => {
            ws.send(JSON.stringify({ type: "register", id: userId, role: "joiner" }));
        };

        async function startStream() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                localVideoElem.srcObject = localStream;
                ws.send(JSON.stringify({ type: "requestOffer", target: targetId, from: userId }));
            } catch (error) {
                console.error("Error accessing media devices.", error);
            }
        }

        function setupPeerConnection(callerId) {
            peerConnection = new RTCPeerConnection({ iceServers: [{ urls: "stun:stun.l.google.com:19302" }] });
            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
            peerConnection.onicecandidate = event => {
                if (event.candidate) {
                    ws.send(JSON.stringify({ type: "candidate", target: callerId, candidate: event.candidate, from: userId }));
                }
            };
            peerConnection.ontrack = event => {
                remoteVideoElem.srcObject = event.streams[0];
            };
            return peerConnection;
        }

        ws.onmessage = async (message) => {
            const data = JSON.parse(message.data);
            if (data.type === "offer") {
                if (!peerConnection) peerConnection = setupPeerConnection(data.from);
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                ws.send(JSON.stringify({ type: "answer", target: data.from, answer, from: userId }));
            } else if (data.type === "candidate" && peerConnection) {
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            } else if (data.type === "endCall") {
                endCall();
            }
        };

        document.getElementById("endCall").addEventListener("click", endCall);
        startStream();
    </script>
</body>
</html>
