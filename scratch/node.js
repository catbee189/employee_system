const io = require("socket.io")(3000, {
    cors: { origin: "*" }
});

io.on("connection", socket => {
    socket.on("offer", offer => socket.broadcast.emit("offer", offer));
    socket.on("answer", answer => socket.broadcast.emit("answer", answer));
    socket.on("ice-candidate", candidate => socket.broadcast.emit("ice-candidate", candidate));
});
