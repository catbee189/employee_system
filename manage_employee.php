<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
require "cofig.php";

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch employees with pagination
$query = "SELECT * FROM employees LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$employees = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get total records for pagination
$total_query = "SELECT COUNT(*) as total FROM employees";
$total_result = mysqli_query($conn, $total_query);
$total_records = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_records / $limit);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    /* Profile image container */
    .profile-image-container {
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        overflow: hidden;
        width: 100px;
        height: 100px;
        border: 2px solid #ddd; /* Light border around the profile image */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition effect */
    }

    /* Profile image styling */
    .profile-image {
        width: 40%;
        height: 40%;
        object-fit: cover; /* Ensures the image covers the entire container */
        border-radius: 50%; /* Circular crop */
    }

    /* Hover effect */
    .profile-image-container:hover {
        transform: scale(1.05); /* Slight zoom effect */
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
    }
</style>
<body>
    <div class="content">
        <h2>Employee List</h2>
        <div class="d-flex justify-content-between mb-3">
            <input class="form-control me-2" type="search" placeholder="Search Employees" id="searchInput">
            <a href="add_employee.php" class="btn btn-success">Add Employee</a>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="employeeResults">
                <?php foreach ($employees as $row): ?>
                    <tr>
                        <td><img src='employee_profile/<?= htmlspecialchars($row['profile_image']) ?>' alt='Profile' class='profile-image'></td>
                        <td><?= htmlspecialchars($row['employee_name']) ?></td>
                        <td><?= htmlspecialchars($row['employeeID']) ?></td>
                        <td>+63 <?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <a href='edit_employee.php?id=<?= $row['id'] ?>' class='btn btn-primary btn-sm'>Edit</a>
                            <button class='btn btn-danger btn-sm delete-btn' data-id='<?= $row['id'] ?>'>Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a></li><?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?><li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>">Next</a></li><?php endif; ?>
            </ul>
        </nav>
    </div>

    <script>
    $(document).ready(function() {
        $(".delete-btn").click(function() {
            let employeeId = $(this).data("id");
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "delete_employee.php",
                        type: "POST",
                        data: { id: employeeId },
                        success: function(response) {
                            if (response === "success") {
                                Swal.fire("Deleted!", "Employee has been removed.", "success").then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire("Error!", "Something went wrong.", "error");
                            }
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
<?php include("./layouts/footer.php"); ?>
