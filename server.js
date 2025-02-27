const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const mysql = require("mysql");

const app = express();
const server = http.createServer(app);
const io = socketIo(server);

const db = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "hcc_pms"
});

db.connect(err => {
    if (err) throw err;
    console.log("Connected to database");
});

let usersInCall = {}; // Store users in each group
let connectedUsers = {}; // Track all connected users

io.on("connection", (socket) => {
    console.log("User connected:", socket.id);

    // Handle user joining call
    socket.on("joinCall", (groupId, userId) => {
        if (!usersInCall[groupId]) usersInCall[groupId] = [];
        usersInCall[groupId].push({ userId, socketId: socket.id });
        connectedUsers[socket.id] = userId; // Track connected users

        socket.join(groupId);
        db.query("INSERT INTO call_logs_group (group_id, user_id, action) VALUES (?, ?, 'joined')", [groupId, userId]);

        io.to(groupId).emit("userConnected", userId);
        console.log(`User ${userId} joined group ${groupId}`);
    });

    // Handle user declining the call
    socket.on("declineCall", (groupId, userId) => {
        db.query("INSERT INTO call_logs (group_id, user_id, action) VALUES (?, ?, 'declined')", [groupId, userId]);
        console.log(`User ${userId} declined call in group ${groupId}`);
    });

    // Handle user ending the call
    socket.on("endCall", (groupId, userId) => {
        socket.to(groupId).emit("callEnded");

        db.query("INSERT INTO call_logs (group_id, user_id, action) VALUES (?, ?, 'ended')", [groupId, userId]);
        
        usersInCall[groupId] = usersInCall[groupId].filter(user => user.userId !== userId);
        delete connectedUsers[socket.id];

        console.log(`User ${userId} ended call in group ${groupId}`);
    });

    // Handle user disconnection
    socket.on("disconnect", () => {
        console.log("User disconnected:", socket.id);
        const userId = connectedUsers[socket.id];

        for (const group in usersInCall) {
            usersInCall[group] = usersInCall[group].filter(user => user.socketId !== socket.id);
        }

        delete connectedUsers[socket.id];

        if (userId) {
            db.query("INSERT INTO call_logs (user_id, action) VALUES (?, 'disconnected')", [userId]);
            console.log(`User ${userId} disconnected`);
        }
    });
});

server.listen(3000, () => {
    console.log("Server running on http://localhost:3000");
});
