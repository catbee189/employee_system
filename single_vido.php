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
    <title>Caller</title>
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
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

    <h1>Caller</h1>
    <video id="localVideo" autoplay playsinline muted></video>
    <div id="remoteVideo"></div>

    <div class="controls">
        <button id="toggleCamera">Camera Off</button>
        <button id="toggleMic">Mute</button>
        <button id="endCall">End Call</button>
    </div>

    <?php 
    $sql = "SELECT * FROM call_logs ORDER BY start_time DESC";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc(); 
    if ($row && $row['status'] == "declined") :
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
                    <h5 class="modal-title">Call Declined</h5>
                </div>
                <div class="modal-body">
                    <p>Your call was declined. Please try again later.</p>
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
        let userId = "caller";
        let cameraEnabled = true;
        let micEnabled = true;

        ws.onopen = () => ws.send(JSON.stringify({ type: "register", id: userId }));

        async function startCall() {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById("localVideo").srcObject = localStream;
        }

        async function handleJoiner(joinerId) {
            if (!localStream) await startCall();
            if (!peerConnection) {
                peerConnection = new RTCPeerConnection({ iceServers: [{ urls: "stun:stun.l.google.com:19302" }] });
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                peerConnection.onicecandidate = event => {
                    if (event.candidate) {
                        ws.send(JSON.stringify({ type: "candidate", target: joinerId, candidate: event.candidate, from: userId }));
                    }
                };
                peerConnection.ontrack = event => {
                    let remoteVideo = document.getElementById("video_" + joinerId);
                    if (!remoteVideo) {
                        remoteVideo = document.createElement("video");
                        remoteVideo.id = "video_" + joinerId;
                        remoteVideo.autoplay = true;
                        remoteVideo.playsInline = true;
                        document.getElementById("remoteVideo").appendChild(remoteVideo);
                    }
                    remoteVideo.srcObject = event.streams[0];
                };
            }

            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            ws.send(JSON.stringify({ type: "offer", target: joinerId, offer, from: userId }));
        }

        ws.onmessage = async (message) => {
            const data = JSON.parse(message.data);
            if (data.type === "requestOffer" && data.from) {
                handleJoiner(data.from);
            } else if (data.type === "answer" && data.from) {
                if (peerConnection) {
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
                }
            } else if (data.type === "candidate" && data.from) {
                if (peerConnection) {
                    await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                }
            } else if (data.type === "endCall" && data.from) {
                if (peerConnection) {
                    peerConnection.close();
                    peerConnection = null;
                    const remoteVideo = document.getElementById("video_" + data.from);
                    if (remoteVideo) {
                        remoteVideo.srcObject = null;
                    }
                }
            }
        };

        async function endCall() {
            ws.send(JSON.stringify({ type: "endCall", from: userId }));
            document.body.innerHTML += "<div style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 24px; color: white;'>Call Ended. Redirecting...</div>";
            setTimeout(() => {
                window.location.href = 'call.php';
            }, 3000);
        }

        document.getElementById("endCall").addEventListener("click", endCall);

        document.getElementById("toggleCamera").addEventListener("click", () => {
            cameraEnabled = !cameraEnabled;
            localStream.getVideoTracks()[0].enabled = cameraEnabled;
            document.getElementById("toggleCamera").textContent = cameraEnabled ? "Camera Off" : "Camera On";
        });

        document.getElementById("toggleMic").addEventListener("click", () => {
            micEnabled = !micEnabled;
            localStream.getAudioTracks()[0].enabled = micEnabled;
            document.getElementById("toggleMic").textContent = micEnabled ? "Mute" : "Unmute";
        });
        function checkCallStatus() {
        fetch("check_call_status.php")
            .then(response => response.json())
            .then(data => {
                if (data.status === "declined") {
                    var modal = new bootstrap.Modal(document.getElementById('incomingCallModal'));
                    modal.show();
                }
            })
            .catch(error => console.error("Error fetching call status:", error));
    }

    // Check call status every 3 seconds (adjust as needed)
    setInterval(checkCallStatus, 3000);
        window.onload = startCall;
    </script>
</body>
</html>
