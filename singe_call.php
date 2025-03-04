<?php
include("./cofig.php");

// Dummy login system for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1, 1000); 
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$receiver_id) {
    die("Invalid user ID.");
}


// Fetch receiver's name and image from `employee` and `admin` tables
$query = $conn->prepare("
    SELECT firstname, lastname, profile_image, 'employee' AS user_type, id FROM employees WHERE id = ? 
    UNION 
    SELECT firstname, lastname, profile_image, 'admin' AS user_type, id FROM admin WHERE id = ?
");
$query->bind_param("ii", $receiver_id, $receiver_id);
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {
    $receiver_name = $row['firstname'] . " " . $row['lastname'];
    $receiver_image = !empty($row['profile_image']) ? $row['profile_image'] : 'default.jpg'; // Default image if none
    $receiver_id = $row['id']; // Use the receiver's ID
} else {
    $receiver_name = "Unknown User";
    $receiver_image = 'default.jpg';

    
}




$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call</title>
    <style>
        /* Page background (blue) */
        body {
            background-color: #007bff; /* Blue background */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Center the profile card */
        .profile-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Card styling with black border */
        .profile-card {
            background: white;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            width: 300px;
            border: 2px solid black; /* Black border for the card */
        }

        /* Profile image styling */
        .profile-card img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #28a745; /* Green border around image */
        }

        /* User name styling */
        .profile-card h1 {
            font-size: 24px;
            margin-top: 15px;
            color: #333;
        }

        /* Button styling */
        .start-call-btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
        }

        /* Button hover effect */
        .start-call-btn:hover {
            background-color: #218838;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 600px) {
            .profile-card {
                width: 90%;
            }
        }



        
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <!-- Display the profile image -->
            <img src="uploads/<?php echo htmlspecialchars($receiver_image); ?>" alt="Profile Image">

            <!-- Display the user's name -->
            <h1><?php echo htmlspecialchars($receiver_name); ?></h1>

            <!-- Start video call button -->
            <button id="startCall" class="start-call-btn">Start Video Call</button>

        </div>
    </div>
</body>
<script>
        document.getElementById("startCall").onclick = function() {
            // Update call status and redirect
            fetch(`update_call_status.php?user_id=<?php echo $receiver_id; ?>`)
                .then(response => {
                    if (response.ok) {
                        window.location.href = `single_vido.php?user_id=<?php echo $receiver_id; ?>`;
                    } else {
                        alert('Failed to update call status.');
                    }
                });
        }
    </script>
</html>
