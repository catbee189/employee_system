<?php
session_start();
include("./cofig.php");

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1, 1000); // Replace with actual login logic
}

$user_id = $_SESSION['user_id'];
$caller_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$caller_id) {
    die("Invalid user ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Joiner</title>
    <style>
        body { text-align: center; background: black; color: white; }
        video { border: 2px solid white; width: 45%; }
    </style>
</head>
<body>
    <h1>Joiner</h1>
    <video id="localVideo" autoplay playsinline muted></video>
    <video id="remoteVideo" autoplay playsinline></video>

    <div class="controls">
        <button id="leaveCall">Leave</button>
    </div>

    <script>
        const ws = new WebSocket("ws://localhost:3001");
        let peerConnection = null;
        let localStream;
        let userId = "<?php echo $user_id; ?>";
        let callerId = "<?php echo $caller_id; ?>";

        ws.onopen = () => {
            ws.send(JSON.stringify({ type: "register", id: userId }));
            startStream(); // Start the local stream when connected
        };

        async function startStream() {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById("localVideo").srcObject = localStream;

            peerConnection = new RTCPeerConnection();
            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        }

        ws.onmessage = async (message) => {
            const data = JSON.parse(message.data);
            if (data.type === "offer") {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                ws.send(JSON.stringify({ type: "answer", target: data.from, answer, from: userId }));

            } else if (data.type === "answer") {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            } else if (data.type === "iceCandidate") {
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        };

        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                ws.send(JSON.stringify({ type: "iceCandidate", candidate: event.candidate, target: callerId, from: userId }));
            }
        };

        peerConnection.ontrack = (event) => {
            const remoteVideo = document.getElementById("remoteVideo");
            remoteVideo.srcObject = event.streams[0];
        };

        async function leaveCall() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            ws.send(JSON.stringify({ type: "leaveCall", from: userId }));
            document.body.innerHTML += "<div>You have left the call.</div>";
        }

        document.getElementById("leaveCall").addEventListener("click", leaveCall);
        window.onload = startStream;
    </script>
</body>
</html>
