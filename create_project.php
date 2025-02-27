<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<?php
include('cofig.php');
include('modals.html');

// Check if the form is submitted
if (isset($_POST['create_project'])) {
    // Get project details from the form
    $project_name = mysqli_real_escape_string($conn, $_POST['project_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $budget = !empty($_POST['budget']) ? mysqli_real_escape_string($conn, $_POST['budget']) : null;
    $task = !empty($_POST['task']) ? mysqli_real_escape_string($conn, $_POST['task']) : null;

    // Insert project details into the projects table
    $insert_project_query = "INSERT INTO projects (project_name, description, start_date, due_date, budget)
                             VALUES ('$project_name', '$description', '$start_date', '$due_date', " . ($budget ? "'$budget'" : "NULL") . ")";
    
    if (mysqli_query($conn, $insert_project_query)) {
        $project_id = mysqli_insert_id($conn); // Get the ID of the newly created project

        // Save assigned employees
        if (!empty($_POST['assigned_employees'])) {
            $assigned_employees = json_decode($_POST['assigned_employees'], true);

            foreach ($assigned_employees as $employee) {
                $employee_id = $employee['id'];
                $project_role = mysqli_real_escape_string($conn, $employee['role']);

                $insert_employee_query = "INSERT INTO project_employees (project_id, employee_id, projectRole,task)
                                          VALUES ('$project_id', '$employee_id', '$project_role','$task')";

                mysqli_query($conn, $insert_employee_query);
            }
        }

        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalMessage = document.getElementById('modalMessage');
                modalMessage.textContent = 'Project created successfully';
                var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                messageModal.show();
                messageModal._element.addEventListener('hidden.bs.modal', function () {
                    window.location = 'manage_project.php';
                });
            });
        </script>";
    } else {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalMessage = document.getElementById('modalMessage');
                modalMessage.textContent = 'Error: " . mysqli_error($conn) . "';
                var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                messageModal.show();
            });
        </script>";
    }

    // Close the connection
    mysqli_close($conn);
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>