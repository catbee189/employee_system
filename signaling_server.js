const WebSocket = require("ws");
const wss = new WebSocket.Server({ port: 8080 });

let clients = {}; // Store connected clients

wss.on("connection", (ws, req) => {
    const userId = req.url.split("?user_id=")[1]; // Get user ID from URL
    clients[userId] = ws;

    ws.on("message", message => {
        const data = JSON.parse(message);
        if (clients[data.target]) {
            clients[data.target].send(JSON.stringify(data));
        }
    });

    ws.on("close", () => {
        delete clients[userId];
    });
});
socket.onmessage = async (event) => {
    const data = JSON.parse(event.data);
    if (data.type === "offer") {
        const peer = createPeerConnection(data.from);
        await peer.setRemoteDescription(new RTCSessionDescription(data.offer));
        const answer = await peer.createAnswer();
        await peer.setLocalDescription(answer);
        socket.send(JSON.stringify({ type: "answer", target: data.from, answer }));
    } else if (data.type === "answer") {
        await peerConnections[data.from].setRemoteDescription(new RTCSessionDescription(data.answer));
    }
};