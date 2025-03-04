const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 3001 }, () => {
    console.log("WebSocket Server running on ws://localhost:3001");
});

let clients = {};

wss.on('connection', ws => {
    ws.on('message', message => {
        const data = JSON.parse(message);

        if (data.type === "register") {
            clients[data.id] = ws;
            console.log(`${data.id} registered`);
        } else if (data.type === "endCall") {
            if (clients[data.target]) {
                clients[data.target].send(JSON.stringify({ type: "endCall" }));
            }
        } else if (data.type === "videoControl") {
            // Broadcast camera toggle to all clients
            Object.values(clients).forEach(client => {
                client.send(JSON.stringify({
                    type: "videoControl",
                    id: data.id,
                    cameraOn: data.cameraOn
                }));
            });
        } else if (clients[data.target]) {
            clients[data.target].send(JSON.stringify(data)); // Forward message
        }
    });

    ws.on('close', () => {
        for (let id in clients) {
            if (clients[id] === ws) delete clients[id];
        }
    });
});
