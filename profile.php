<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
require 'cofig.php'; // Ensure correct file name

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo "<script>alert('Please log in first'); window.location='login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Determine the correct table based on the role
if ($role === "admin") {
    $table = "admin";
} elseif ($role === "employee") {
    $table = "employees";
} elseif ($role === "project manager") {
    $table = "employees";
} elseif ($role === "super_admin") {
    $table = "admin";
} else {
    echo "<script>alert('Invalid user role.'); window.location='login.html';</script>";
    exit();
}

// Fetch user details
$query = "SELECT firstname, lastname, email, role, profile_image FROM $table WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<script>alert('User not found.'); window.location='login.html';</script>";
    exit();
}

// Default profile image if none is set
$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'default-profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <img src="uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="profile-img">
        <h2><?php echo htmlspecialchars($user['firstname']); ?> <?php echo htmlspecialchars($user['lastname']); ?></h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</body>
</html>
<?php include("./layouts/footer.php"); ?>