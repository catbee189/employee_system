    <?php
    include("./layouts/header.php");
    include("./layouts/sidebar.php");
    require "cofig.php";


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_task'])) {
        // Sanit$employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
        $employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;

        $projectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        // $employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;

        // Validate project and employee IDs
        if ($projectId > 0 && $employeeId > 0) {
            // Directory to store uploaded files
            $uploadDir = 'project_files/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadedFilePaths = [];

            // Fetch project name from projects table
            $projectNameQuery = $conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
            $projectNameQuery->bind_param("i", $projectId);
            $projectNameQuery->execute();
            $projectNameResult = $projectNameQuery->get_result();
            $projectNameRow = $projectNameResult->fetch_assoc();
            $projectName = $projectNameRow['project_name'] ?? 'project';
            $projectNameQuery->close();

            // Fetch task from project_employees table
            $taskQuery = $conn->prepare("SELECT task FROM project_employees WHERE project_id = ? AND employee_id = ?");
            $taskQuery->bind_param("ii", $projectId, $employeeId);
            $taskQuery->execute();
            $taskResult = $taskQuery->get_result();
            $taskRow = $taskResult->fetch_assoc();
            $taskName = $taskRow['task'] ?? 'task';
            $taskQuery->close();

            // Handle file uploads
            foreach ($_FILES['projectFiles']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['projectFiles']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = basename($_FILES['projectFiles']['name'][$key]);
                    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);

                    // Generate the new filename with format "project_name-task-unique_identifier"
                    $uniqueId = uniqid();
                    $newFileName = $taskName . '-' . $uniqueId . '.' . $fileExtension;
                    $destination = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmpName, $destination)) {
                        $uploadedFilePaths[] = $destination;
                    } else {
                        echo "<div class='alert alert-danger'>Failed to upload file: {$originalName}</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Error uploading file: " . $_FILES['projectFiles']['name'][$key] . "</div>";
                }
            }

            if (!empty($uploadedFilePaths)) {
                // Convert the file paths array to a JSON string for storage
                $filesJson = json_encode($uploadedFilePaths);

                // Update the project_employees table
                $updateQuery = $conn->prepare("UPDATE project_employees SET projectFile = ?, task_status = 'submitted' WHERE project_id = ? AND employee_id = ?");
                $updateQuery->bind_param("sii", $filesJson, $projectId, $employeeId);

                if ($updateQuery->execute()) {
                    // Calculate project progress after marking the task as "done"
                    $totalTasksQuery = $conn->prepare("SELECT COUNT(*) AS totalTasks FROM project_employees WHERE project_id = ?");
                    $totalTasksQuery->bind_param("i", $projectId);
                    $totalTasksQuery->execute();
                    $totalTasksResult = $totalTasksQuery->get_result();
                    $totalTasks = $totalTasksResult->fetch_assoc()['totalTasks'];
                    $totalTasksQuery->close();
                    $completedTasksQuery = $conn->prepare("
                    SELECT COUNT(*) AS completedTasks 
                    FROM project_employees 
                    WHERE project_id = ? AND task_status IN ('submitted', 'done')
                ");
                    $completedTasksQuery->bind_param("i", $projectId);
                    $completedTasksQuery->execute();
                    $completedTasksResult = $completedTasksQuery->get_result();
                    $completedTasks = $completedTasksResult->fetch_assoc()['completedTasks'];
                    $completedTasksQuery->close();

                    // Calculate the progress as a percentage
                    $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

                    // Update the progress in the projects table
                    $updateProgressQuery = $conn->prepare("UPDATE projects SET progress = ? WHERE project_id = ?");
                    $updateProgressQuery->bind_param("di", $progress, $projectId);
                    $updateProgressQuery->execute();
                    $updateProgressQuery->close();

                    echo "<div class='alert alert-success'>Task submitted successfully and project progress updated to " . round($progress, 2) . "%!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Database update failed: " . $conn->error . "</div>";
                }

                $updateQuery->close();
            } else {
                echo "<div class='alert alert-warning'>No files were uploaded.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid project or employee ID.</div>";
        }
    }

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
            AND pe.projectRole != 'Project Manager'");
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

    } else {
        echo "<p class='text-danger'>No project ID specified.</p>";
        exit();
    }

    $projectManagerQuery = $conn->prepare("
        SELECT e.employee_name 
        FROM project_employees pe
        JOIN employees e ON pe.employee_id = e.id
        WHERE pe.project_id = ? AND pe.projectRole = 'Project Manager'
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
        <div class="wrapper">
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
            <!-- Replace the existing Submit Task button -->
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitTaskModal">
        Submit Task
    </button>

            <br><br>
            <a href="employee_projects.php" class="btn btn-primary">Back to Projects</a>
                </div>
            </div>
        </div>
    </div>
        
    <!-- Submit Task Modal -->
    <div class="modal fade" id="submitTaskModal" tabindex="-1" aria-labelledby="submitTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="submitTaskForm" action="" method="POST" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="submitTaskModalLabel">Submit Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <!-- File Upload Section -->
            <div id="file-upload-section">
                <div class="mb-3 file-input-group">
                <label for="projectFiles[]" class="form-label">Upload File(s)</label>
                <div class="input-group">
                    <input type="file" name="projectFiles[]" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary add-file-input">+</button>
                </div>
                </div>
            </div>
            <!-- Hidden input to pass project ID -->
            <input type="text" value="<?php echo $project['project_id']; ?>"name="project_id" >
            <!-- Hidden input to pass employee ID (assuming single employee submission) -->
            <input type="hidden" name="employee_id" value="<?php 
                // Assuming you want to submit for a specific employee, adjust as needed
                // For demonstration, taking the first employee
                echo !empty($assignedEmployees) ? intval($assignedEmployees[0]['id']) : 0; 
            ?>">
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="submit_task" class="btn btn-primary">Submit</button>
            </div>
        </div>
        </form>
    </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('file-upload-section').addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('add-file-input')) {
            e.preventDefault();
            const fileInputGroup = document.createElement('div');
            fileInputGroup.classList.add('mb-3', 'file-input-group');
            fileInputGroup.innerHTML = `
            <label class="form-label">Upload Files</label>
            <div class="input-group">
                <input type="file" name="projectFiles[]" class="form-control" required>
                <button type="button" class="btn btn-outline-danger remove-file-input">-</button>
            </div>
            `;
            document.getElementById('file-upload-section').appendChild(fileInputGroup);
        }

        if (e.target && e.target.classList.contains('remove-file-input')) {
            e.preventDefault();
            e.target.closest('.file-input-group').remove();
        }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
    </html>     

    <?php include("./layouts/footer.php"); ?>

