const WebSocket = require("ws");

const wss = new WebSocket.Server({ port: 3001 });
let users = {}; // Stores connected users

wss.on("connection", (ws) => {
    ws.on("message", (message) => {
        const data = JSON.parse(message);

        switch (data.type) {
            case "register":
                users[data.userId] = ws;
                broadcast({ type: "user_joined", userId: data.userId }, ws);
                break;
            case "offer":
            case "answer":
            case "candidate":
            case "endCall":
                if (users[data.target]) {
                    users[data.target].send(JSON.stringify(data));
                }
                break;
        }
    });

    ws.on("close", () => {
        Object.keys(users).forEach((userId) => {
            if (users[userId] === ws) {
                delete users[userId];
                broadcast({ type: "user_left", userId });
            }
        });
    });
});

function broadcast(data, excludeWs) {
    Object.values(users).forEach((client) => {
        if (client !== excludeWs) {
            client.send(JSON.stringify(data));
        }
    });
}

console.log("WebSocket Server is running on ws://localhost:3001");
