<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caller</title>
    <style>
        body { text-align: center; background: black; color: white; }
        video { width: 40%; border: 2px solid white; }
    </style>
</head>
<body>
    <h1>Caller</h1>
    <video id="localVideo" autoplay playsinline muted></video>
    <video id="remoteVideo" autoplay playsinline></video>
    <br>
    <button id="startCall">Start Video Call</button>
    <button id="endCall">End Call</button>

    <script>
        const socket = new WebSocket("ws://localhost:3000");
        const peerConnection = new RTCPeerConnection({ iceServers: [{ urls: "stun:stun.l.google.com:19302" }] });

        let localStream;

        socket.onopen = () => socket.send(JSON.stringify({ type: "register", id: "caller" }));

        navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(stream => {
            document.getElementById("localVideo").srcObject = stream;
            localStream = stream;
            stream.getTracks().forEach(track => peerConnection.addTrack(track, stream));
        });

        document.getElementById("startCall").addEventListener("click", async () => {
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            socket.send(JSON.stringify({ type: "offer", offer, target: "joiner" }));
        });

        socket.onmessage = async event => {
            const data = JSON.parse(event.data);
            if (data.type === "answer") {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            } else if (data.type === "candidate") {
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        };

        peerConnection.onicecandidate = event => {
            if (event.candidate) {
                socket.send(JSON.stringify({ type: "candidate", candidate: event.candidate, target: "joiner" }));
            }
        };

        peerConnection.ontrack = event => {
            document.getElementById("remoteVideo").srcObject = event.streams[0];
        };

        document.getElementById("endCall").addEventListener("click", () => {
            peerConnection.close();
            socket.close();
        });
    </script>
</body>
</html>
