<?php 

include("./layouts/header.php");
include("./layouts/sidebar.php");
include("cofig.php"); // Database connection

// Fetch logged-in user role

$user_id = $_SESSION['user_id'];

// Check if the user is an admin, super admin, or employee
$query = "SELECT role FROM admin WHERE id = '$user_id'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $loggedInUser = $result->fetch_assoc();
    $loggedInRole = $loggedInUser['role'];
} else {
    $query = "SELECT role FROM employees WHERE id = '$user_id'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $loggedInUser = $result->fetch_assoc();
        $loggedInRole = $loggedInUser['role'];
    } else {
        die("Error fetching user role.");
    }
}

// Define query based on role
$users = [];
if ($loggedInRole == 'super_admin') {
    $query1 = "SELECT * FROM admin WHERE role = 'admin' AND id != '$user_id'";
    $query2 = "SELECT * FROM admin WHERE role = 'employee' AND id != '$user_id'";
} elseif ($loggedInRole == 'admin') {
    $query1 = "SELECT * FROM admin WHERE role = 'super_admin' AND id != '$user_id'";
    $query2 = "SELECT * FROM employees WHERE role IN ('employee', 'project manager') AND id != '$user_id'";


} elseif ($loggedInRole == 'project manager') {
    $query1 = "SELECT * FROM admin WHERE role = 'admin' AND id != '$user_id'";
    $query2 = "SELECT * FROM employees WHERE role IN ('employee', 'project manager') AND id != '$user_id'";
} elseif ($loggedInRole == 'employee') {
    $query1 = "SELECT * FROM admin WHERE role = 'admin' AND id != '$user_id'";
    $query2 = "SELECT * FROM employees WHERE role = 'project manager' AND id != '$user_id'";
} else {
    die("Unauthorized access.");
}

$result1 = $conn->query($query1);
$result2 = $conn->query($query2);

if ($result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $users[] = $row;
    }
}

if ($result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $users[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 700px; background: white; padding: 20px; border-radius: 10px; }
        .user-avatar { border-radius: 50%; width: 50px; height: 50px; margin-right: 10px; }
        .user-status { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .online { background-color: green; }
        .offline { background-color: gray; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h4 class="text-center">Users</h4>
    <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
    <br>
<!-- Button only visible if the user is an admin -->
 <!-- Admin Only: Create Group -->
 <?php if ($_SESSION['role'] === "admin") : ?>
        <input type="text" id="groupName" placeholder="Enter group name">
        <button onclick="createGroup()">Create Group</button>
    <?php endif; ?>
 <ul class="list-group mt-3" id="employeeList">
        <?php
        if (!empty($users)) {
            foreach ($users as $row) {
                $userId = $row['id'];
                $userName = htmlspecialchars($row['firstname']);
                $userStatus = strtolower($row['status']) === "active" ? "online" : "offline";
                $userAvatar = !empty($row['profile_image']) ? $row['profile_image'] : "default-avatar.jpg";
                echo '<li class="list-group-item d-flex align-items-center" data-name="'.$userName.'">
                    <img src="uploads/'.$userAvatar.'" alt="User Avatar" class="user-avatar">
                    <span class="user-status '.$userStatus.'"></span>
                    <span class="ml-2">'.$userName.' ('.$row['role'].')</span>
                    <div class="ml-auto">
                        <a href="message.php?user_id='.$userId.'" class="btn btn-primary btn-sm">Message</a>
                        <button class="btn btn-success btn-sm">Call</button>
                    </div>
                </li>';
            }
        } else {
            echo "<p class='text-center'>No users found.</p>";
        }
        ?>
    </ul>
<?php
    $query = "SELECT g.id, g.group_name 
          FROM groups g
        ";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

 <br>
    <h2>Your Groups</h2>
    <ul>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <li>
              <td>
              <a href="chat.php?group_id=<?= $row['id']; ?>" class="btn btn-success"><?= htmlspecialchars($row['group_name']); ?></a>

              </td>
            </li>
        <?php endwhile; ?>
    </ul>
</div>
<script>
$(document).ready(function(){
    $("#searchInput").on("keyup", function() {
        var searchText = $(this).val().toLowerCase();
        $("#employeeList li").each(function() {
            var name = $(this).attr("data-name").toLowerCase();
            $(this).toggle(name.includes(searchText));
        });
    });
});
</script>

<script>
        function createGroup() {
            let groupName = document.getElementById("groupName").value;
            if (!groupName) {
                alert("Enter a group name");
                return;
            }
            fetch("create_group.php", {
                method: "POST",
                body: new URLSearchParams({ group_name: groupName }),
                headers: { "Content-Type": "application/x-www-form-urlencoded" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Group Created!");
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
        }
    </script>

</body>
</html>
<?php include("./layouts/footer.php"); ?> 
