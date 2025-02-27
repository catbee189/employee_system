<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
require "cofig.php";

// Pagination setup
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch project data with pagination
$query = "SELECT * FROM projects LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$projects = [];
while ($row = mysqli_fetch_assoc($result)) {
    $currentDate = date('Y-m-d');
    $dueDate = $row['due_date'];
    $progress = $row['progress'];

    if ($progress == 100) {
        $newStatus = 'Completed';
    } elseif ($currentDate > $dueDate) {
        $newStatus = 'Passed Due';
    } else {
        $newStatus = 'Ongoing';
    }

    if ($row['status'] != $newStatus) {
        $updateQuery = "UPDATE projects SET status = '$newStatus' WHERE project_id = " . $row['project_id'];
        mysqli_query($conn, $updateQuery);
        $row['status'] = $newStatus;
    }

    $projects[] = $row;
}

// Get total number of projects for pagination
$total_query = "SELECT COUNT(*) as total FROM projects";
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
<body>
    <div class="content">
        <h2>Project List</h2>
        <div class="d-flex justify-content-between mb-3">
            <div class="mx-auto">
                <input class="form-control" type="search" placeholder="Search Projects" id="searchInput">
            </div>
            <div>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>description</th>
                    <th>Budget</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['project_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['budget']) ?></td>
                        <td><?= htmlspecialchars($row['start_date']) ?></td>
                        <td><?= htmlspecialchars($row['due_date']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                        <a href="view_attach_file.php?id=<?= $row['project_id'] ?>" class="btn btn-info btn-sm">Details</a>                           
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script>
        $(document).ready(function() {
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val();
                $.ajax({
                    url: 'search_projects.php',
                    type: 'GET',
                    data: { search: searchTerm },
                    success: function(data) {
                        $('tbody').html(data);
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php include("./layouts/footer.php"); ?>
