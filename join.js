// JavaScript for WebRTC (script.js)
let localStream;
let remoteStream;
const joinCallButton = document.getElementById("joinCall");
const endCallButton = document.getElementById("endCall");
const localVideo = document.getElementById("localVideo");
const remoteVideo = document.getElementById("remoteVideo");
const videoPopup = document.getElementById("videoPopup");

startCallButton.addEventListener("click", async () => {
    videoPopup.style.display = "block";
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
    } catch (error) {
        console.error("Error accessing media devices.", error);
    }
});

joinCallButton.addEventListener("click", async () => {
    try {
        remoteStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        remoteVideo.srcObject = remoteStream;
    } catch (error) {
        console.error("Error accessing media devices.", error);
    }
});

endCallButton.addEventListener("click", () => {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    if (remoteStream) {
        remoteStream.getTracks().forEach(track => track.stop());
    }
    videoPopup.style.display = "none";
});
