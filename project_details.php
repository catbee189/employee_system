<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
// Include the database configuration file
include('cofig.php');

// Get the project ID from the URL
if (isset($_GET['id'])) {
    $projectId = intval($_GET['id']); // Sanitize input

    // Fetch project data from the database
    $projectQuery = $conn->prepare("SELECT * FROM projects WHERE project_id = ?");
    $projectQuery->bind_param("i", $projectId);
    $projectQuery->execute();
    $projectResult = $projectQuery->get_result();
    $project = $projectResult->fetch_assoc();

    // Check if project exists
    if (!$project) {
        echo "<p class='text-danger'>Project not found.</p>";
        exit();
    }

    // Fetch assigned employees with their projectRole, task, and task_status
    $assignedEmployeesQuery = $conn->prepare("
        SELECT e.id, e.employee_name, pe.projectRole, pe.task, pe.task_status 
        FROM project_employees pe 
        JOIN employees e ON pe.employee_id = e.id 
        WHERE pe.project_id = ?
        AND pe.projectRole !='project manager'");
    $assignedEmployeesQuery->bind_param("i", $projectId);
    $assignedEmployeesQuery->execute();
    $assignedEmployeesResult = $assignedEmployeesQuery->get_result();

    // Create an array to hold employee data
    $assignedEmployees = [];
    while ($row = $assignedEmployeesResult->fetch_assoc()) {
        $assignedEmployees[] = $row;
    }

    // Calculate total tasks and completed tasks
    $totalTasks = count($assignedEmployees);
    $completedTasks = 0;
    
    foreach ($assignedEmployees as $employee) {
        if ($employee['task_status'] === 'done') {
            $completedTasks++;
        }
    }

    // Calculate project progress percentage
    $projectProgress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

    // Round progress percentage
    $progressPercentage = round($projectProgress, 2);

    $updateProgressQuery = $conn->prepare("UPDATE projects SET progress = ? WHERE project_id = ?");
    $updateProgressQuery->bind_param("di", $progressPercentage, $projectId);
    $updateProgressQuery->execute();

} else {
    echo "<p class='text-danger'>No project ID specified.</p>";
    exit();
}

$projectManagerQuery = $conn->prepare("
    SELECT e.employee_name , pe.task
    FROM project_employees pe
    JOIN employees e ON pe.employee_id = e.id
    WHERE pe.project_id = ? AND pe.projectRole ='project manager'
");
$projectManagerQuery->bind_param("i", $projectId);
$projectManagerQuery->execute();
$projectManagerResult = $projectManagerQuery->get_result();
$projectManager = $projectManagerResult->fetch_assoc();

mysqli_close($conn);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

        .required {
            color: red;
        }

        .employee-list {
            list-style-type: none;
            padding: 0;
        }

        .progress {
            height: 30px;
        }
    </style>
</head>
<body>
<div class="content">
    <div class="">
        <div class="container">
            <div class="card">
                <h2 class="card-title">Project Details</h2>
                <p><strong>Project Name:</strong> <?php echo htmlspecialchars($project['project_name']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($project['start_date']); ?></p>
                <p><strong>Due Date:</strong> <?php echo htmlspecialchars($project['due_date']); ?></p>
                <p><strong>Budget:</strong> PHP <?php echo number_format($project['budget'], 2); ?></p>

                <!-- Overall Project Progress Bar -->
                <h5>Project Progress: <?php echo $progressPercentage; ?>%</h5>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo htmlspecialchars($progressPercentage); ?>%;" aria-valuenow="<?php echo htmlspecialchars($progressPercentage); ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo htmlspecialchars($progressPercentage); ?>%
                    </div>
                </div>

                <br>
                <p><strong>Project Manager:</strong>
                    <?php 
                    if ($projectManager) {
                        echo htmlspecialchars($projectManager['employee_name']);
                    } else {
                        echo "No Project Manager assigned.";
                    }
                    ?>
                </p>
                <p><strong>Project Members:</strong></p>
                <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Task</th>
                    <th>Contribution</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($assignedEmployees)) {
                    foreach ($assignedEmployees as $employee) {
                        // Calculate contribution of this task
                        $contribution = $totalTasks > 0 ? (1 / $totalTasks) * 100 : 0;
                        // Check if the task is done
                        $employeeProgress = ($employee['task_status'] === 'done') ? $contribution : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['task']); ?></td>
                            <td>
                                <div class="progress" style="width: 100%;">
                                <?php if ($employee['task_status'] === 'submitted'): ?>
                                            <!-- Full orange bar at 0% when status is "submitted" -->
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                        style="width: <?php echo ($employee['task_status'] === 'submitted') ? '100' : $employeeProgress; ?>%; 
                                        background-color: <?php echo ($employee['task_status'] === 'submitted') ? 'orange' : ''; ?>;" 
                                        aria-valuenow="<?php echo $employeeProgress; ?>" aria-valuemin="0" aria-valuemax="100">
                                        Submitted
                                        <?php elseif ($employee['task_status'] === 'done'): ?>
                                            <!-- Regular progress bar showing actual progress for "done" tasks -->
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                                style="width: <?php echo $employeeProgress; ?>%;" aria-valuenow="<?php echo $employeeProgress; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo round($employeeProgress, 2); ?>%
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                        <!-- <?php echo round($employeeProgress, 2); ?>% -->
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='4'>No employees assigned.</td></tr>";
                }
                ?>
            </tbody>
        </table>
                <a href="view_task_files.php?id=<?php echo $projectId; ?>" class="btn btn-info">View Task Files</a>
                <br><br>
                <a href="manage_project.php" class="btn btn-primary">Back to Projects</a>
            </div>
        </div>
    </div>
</div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php include("./layouts/footer.php"); ?>