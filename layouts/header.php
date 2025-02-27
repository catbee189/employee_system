<?php
session_start();
include("cofig.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // 'admin' or 'employee'

// Determine table based on user type
$table = ($user_type === 'admin') ? "admin" : "employees";

$query = $conn->prepare("SELECT email, role , profile_image FROM $table WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$profileImage = !empty($user['profile_image']) ? "uploads/" . $user['profile_image'] : "uploads/default.png";

// Ensure file exists
if (!file_exists($profileImage)) {
    $profileImage = "uploads/default.png";
}
?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #023B87;">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Project Management System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="rounded-circle" width="40" height="40">
                        <?php echo ucfirst($user['role']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> View Profile</a></li>
                    <li><a class="dropdown-item" href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                         </ul>
                </li>
            </ul>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById("logoutBtn").addEventListener("click", function (e) {
    e.preventDefault(); // Prevent immediate logout

    Swal.fire({
        title: "Are you sure?",
        text: "You will be logged out!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, logout!"
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to logout script (update 'logout.php' as needed)
            window.location.href = "logout.php";
        }
    });
});
</script>
</nav>


