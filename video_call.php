<?php
include("./cofig.php"); 

// Check if group_id is provided
if (!isset($_GET['group_id']) || empty($_GET['group_id'])) {
    die("No group ID provided. Debug: " . var_export($_GET, true));
}

$group_id = intval($_GET['group_id']); // Convert to integer

$sql = "SELECT users.firstname,s.group_id  FROM group_members  s
        JOIN employees users ON s.user_id = users.id 
        WHERE s.group_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row['firstname'];
}
$stmt->close();

if (empty($members)) {
    die("No members found for this group.");
}

// Convert members to JSON for JavaScript
$members_json = json_encode($members);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Video Call</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        #video-container {
            width: 40%;
            height: 40%;
            position: relative;
        }
        video {
            width: 100%;
            height: 100%;
            border: 2px solid black;
        }
        #controls {
            margin-top: 20px;
        }
        button {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Group Video Call</h2>
    <p>Participants:</p>
    <ul id="members-list"></ul>

    <div id="video-container">
        <video id="localVideo" autoplay playsinline></video>
    </div>

    <div id="controls">
        <button onclick="toggleMic()">Mute Mic</button>
        <button onclick="toggleCamera()">Turn Off Camera</button>
        <button onclick="endCall()">End Call</button>
    </div>

    <script>
        // Retrieve members from PHP
        const members = <?php echo $members_json; ?>;

        // Display members on the page
        const membersList = document.getElementById("members-list");
        members.forEach(member => {
            const li = document.createElement("li");
            li.textContent = member;
            membersList.appendChild(li);
        });

        // WebRTC Video Call Setup
        let localStream;

        async function startCall() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                document.getElementById("localVideo").srcObject = localStream;
            } catch (error) {
                console.error("Error accessing media devices.", error);
                alert("Unable to access camera/microphone.");
            }
        }

        function toggleMic() {
            if (localStream) {
                let audioTrack = localStream.getAudioTracks()[0];
                audioTrack.enabled = !audioTrack.enabled;
                alert(audioTrack.enabled ? "Mic Unmuted" : "Mic Muted");
            }
        }

        function toggleCamera() {
            if (localStream) {
                let videoTrack = localStream.getVideoTracks()[0];
                videoTrack.enabled = !videoTrack.enabled;
                alert(videoTrack.enabled ? "Camera On" : "Camera Off");
            }
        }

        function endCall() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        document.getElementById("localVideo").srcObject = null;
        alert("Call Ended");
    }

    // Redirect to chat.php with the correct group_id
    setTimeout(() => {
        window.location.href = "chat.php?group_id=" + <?php echo $group_id; ?>;
    }, 1000);
}



        startCall();
    </script>
</body>
</html>
