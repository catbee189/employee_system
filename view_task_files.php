<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");

include('cofig.php');

if (isset($_GET['id'])) {
    $projectId = intval($_GET['id']);

    // Fetch project files from the database
    $filesQuery = $conn->prepare("
        SELECT pe.id AS pe_id, e.employee_name, pe.projectFile , pe.task_status
        FROM project_employees pe 
        JOIN employees e ON pe.employee_id = e.id 
        WHERE pe.project_id = ? AND pe.projectFile IS NOT NULL");
    $filesQuery->bind_param("i", $projectId);
    $filesQuery->execute();
    $filesResult = $filesQuery->get_result();

    // Create an array to hold file data
    $files = [];
    while ($row = $filesResult->fetch_assoc()) {
        $files[] = $row;
    }
} else {
    echo "<p class='text-danger'>No project ID specified.</p>";
    exit();
}

// Close the connection
mysqli_close($conn);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Task Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #eee !important;
        }
        .wrapper {
            margin-top: 80px;
            margin-bottom: 80px;
        }
        .card {
            padding: 20px;
            background-color: #fff;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="content">
    <div class="wrapper">
        <div class="container">
            <div class="card">
                <h2 class="card-title">Uploaded Task Files</h2>
                <?php if (!empty($files)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>File Name</th>
                                <th>Download</th>
                                <th>Actions</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($file['employee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($file['projectFile']); ?></td>
                                    <td>
                                        <a href="project_files/<?php echo htmlspecialchars($file['projectFile']); ?>" class="btn btn-primary" download>Download</a>
                                    </td>
                                    <td>
                                        <button class="btn btn-success complete-btn" data-id="<?php echo $file['pe_id']; ?>" data-status="done">Complete</button>
                                        <button class="btn btn-danger revise-btn" data-id="<?php echo $file['pe_id']; ?>" data-status="not started">Revise</button>
                                    </td>
                                    <td>
                                    <?php
                                        // Determine the status text based on task_status value
                                        if ($file['task_status'] == 'done') {
                                            echo 'Complete';
                                        } elseif ($file['task_status'] == 'not started') {
                                            echo 'Revise';
                                        } elseif ($file['task_status'] == 'submitted') {
                                            echo 'To Check';
                                        } else {
                                            echo 'Unknown Status'; // Fallback for unexpected values
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No files uploaded for this project.</p>
                <?php endif; ?>
                <a href="project_details.php?id=<?php echo $projectId; ?>" class="btn btn-primary">Back to Project Details</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle the Complete button click
    $('.complete-btn').on('click', function() {
        var peId = $(this).data('id');
        var status = $(this).data('status');

        $.ajax({
            url: 'update_task_status.php',
            type: 'POST',
            data: {
                pe_id: peId,
                task_status: status
            },
            success: function(response) {
                alert('Task status updated to ' + status);
                location.reload(); // Reload the page to show updated status
            },
            error: function() {
                alert('Error updating task status.');
            }
        });
    });

    // Handle the Revise button click
    $('.revise-btn').on('click', function() {
        var peId = $(this).data('id');
        var status = $(this).data('status');

        $.ajax({
            url: 'update_task_status.php',
            type: 'POST',
            data: {
                pe_id: peId,
                task_status: status
            },
            success: function(response) {
                alert('Task status updated to ' + status);
                location.reload(); // Reload the page to show updated status
            },
            error: function() {
                alert('Error updating task status.');
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include("./layouts/footer.php"); ?>