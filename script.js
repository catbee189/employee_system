const ws = new WebSocket("ws://localhost:3001");
let peerConnections = {};
let localStream;
let userId = "user_" + Math.floor(Math.random() * 10000);
let isCaller = document.title === "Caller";
const localVideoElem = document.getElementById("localVideo");
const remoteVideosElem = document.getElementById("remoteVideos");

ws.onopen = () => {
    ws.send(JSON.stringify({ type: "register", id: userId }));
};

async function startStream() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideoElem.srcObject = localStream;
        
        if (isCaller) {
            document.getElementById("startCall").addEventListener("click", startCall);
        }
    } catch (error) {
        console.error("Error accessing media devices.", error);
    }
}

async function startCall() {
    ws.send(JSON.stringify({ type: "requestJoiners" }));
}

function setupPeerConnection(targetId) {
    let peerConnection = new RTCPeerConnection({ iceServers: [{ urls: "stun:stun.l.google.com:19302" }] });

    peerConnections[targetId] = peerConnection;
    
    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

    peerConnection.onicecandidate = event => {
        if (event.candidate) {
            ws.send(JSON.stringify({ type: "candidate", target: targetId, candidate: event.candidate, from: userId }));
        }
    };

    peerConnection.ontrack = event => {
        let remoteVideo = document.createElement("video");
        remoteVideo.autoplay = true;
        remoteVideo.playsInline = true;
        remoteVideo.srcObject = event.streams[0];
        remoteVideosElem.appendChild(remoteVideo);
    };

    return peerConnection;
}

ws.onmessage = async (message) => {
    const data = JSON.parse(message.data);

    if (data.type === "requestJoiners" && !isCaller) {
        ws.send(JSON.stringify({ type: "joinRequest", target: data.from, from: userId }));
    }

    if (data.type === "joinRequest" && isCaller) {
        let peerConnection = setupPeerConnection(data.from);
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        ws.send(JSON.stringify({ type: "offer", target: data.from, offer, from: userId }));
    }

    if (data.type === "offer" && !isCaller) {
        let peerConnection = setupPeerConnection(data.from);
        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        ws.send(JSON.stringify({ type: "answer", target: data.from, answer, from: userId }));
    }

    if (data.type === "answer" && isCaller) {
        await peerConnections[data.from].setRemoteDescription(new RTCSessionDescription(data.answer));
    }

    if (data.type === "candidate") {
        if (peerConnections[data.from]) {
            await peerConnections[data.from].addIceCandidate(new RTCIceCandidate(data.candidate));
        }
    }

    if (data.type === "endCall") {
        endCall();
    }
};

function endCall() {
    Object.values(peerConnections).forEach(peer => peer.close());
    peerConnections = {};
    localStream.getTracks().forEach(track => track.stop());
    localVideoElem.srcObject = null;
    remoteVideosElem.innerHTML = "";
    ws.send(JSON.stringify({ type: "endCall", from: userId }));
}

document.getElementById("endCall").addEventListener("click", endCall);
startStream();
