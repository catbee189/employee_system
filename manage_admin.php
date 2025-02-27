<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");

include("./cofig.php");

$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// Default query to fetch admin data with search filter
$query = "SELECT * FROM admin WHERE firstname LIKE '%$search%' OR lastname LIKE '%$search%' OR email LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get total count for pagination
$total_query = "SELECT COUNT(*) as total FROM admin WHERE firstname LIKE '%$search%' OR lastname LIKE '%$search%' OR email LIKE '%$search%'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
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
        width: 90%;
        height: 90%;
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
        <h2>Admin List</h2>

        <div class="d-flex justify-content-between mb-3">
            <form method="GET" class="d-flex">
                <input class="form-control me-2" type="search" name="search" placeholder="Search admin" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
            <a href="./add_new_user.php" class="btn btn-success">Add Admin</a>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                <th>Profile</th>
                    <th>Admin Name</th>
                    <th>Email</th>
                    <th>Role_as</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($admin)) {
                    foreach ($admin as $row) {
                        echo "<tr>";
                        echo "<td>
                        <div class='profile-image-container'>
                            <img src='uploads/" . htmlspecialchars($row['profile_image']) . "' alt='Profile Image' class='profile-image'>
                        </div>
                  </td>";
            
                        echo "<td>" . htmlspecialchars($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['role']) . "</td>";

                        echo "<td>
                                <a href='edit_user_admin.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>Edit</a>
                                  <a href='javascript:void(0)' class='btn btn-danger btn-sm ms-2 deleteAdmin' data-id='" . $row['id'] . "'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No admin found.</td></tr>";
                } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php if ($page > 1) { ?>
                    <li class="page-item"><a class="page-link" href="?search=<?php echo $search; ?>&page=<?php echo $page - 1; ?>">Previous</a></li>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo $search; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php } ?>
                <?php if ($page < $total_pages) { ?>
                    <li class="page-item"><a class="page-link" href="?search=<?php echo $search; ?>&page=<?php echo $page + 1; ?>">Next</a></li>
                <?php } ?>
            </ul>
        </nav>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
   $(document).ready(function() {
    $('.deleteAdmin').on('click', function() {
        var adminId = $(this).data('id'); // Get the admin ID from the data-id attribute

        // SweetAlert confirmation before deletion
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request to delete the record
                $.ajax({
                    url: 'delete_admin.php', // PHP script for deleting
                    type: 'GET',
                    data: { id: adminId }, // Send the admin ID to the PHP file
                    success: function(response) {
                        try {
                            // Parse the JSON response
                            var res = JSON.parse(response);

                            // Check if the response status is 'success'
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'The admin has been deleted.',
                                }).then(() => {
                                    location.reload(); // Reload the page after success
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: res.message || 'Something went wrong.',
                                });
                            }
                        } catch (e) {
                            // Handle any parsing errors
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Invalid response from the server.',
                            });
                        }
                    },
                    error: function() {
                        // Handle AJAX error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An unexpected error occurred.',
                        });
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
<?php include ('layouts/footer.php'); ?>