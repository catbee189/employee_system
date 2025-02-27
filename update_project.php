<?php
// Include the database configuration file
include('cofig.php');

// Check if project_id is set in POST
if (isset($_POST['project_id'])) {
    // Get the project ID from the POST request
    $projectId = intval($_POST['project_id']);
    
    // Sanitize and validate other inputs
    $projectName = isset($_POST['project_name']) ? trim($_POST['project_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $dueDate = isset($_POST['due_date']) ? $_POST['due_date'] : '';
    $budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
    $employees = isset($_POST['employees']) ? array_map('intval', $_POST['employees']) : []; // Sanitize employee IDs
    $projectRoles = isset($_POST['project_role']) ? $_POST['project_role'] : [];
    $tasks = isset($_POST['task']) ? $_POST['task'] : [];
    
    // Validate the budget
    if ($budget < 0) {
        echo "<script>alert('Budget must be a positive number.'); window.location.href='admin_projects.php';</script>";
        exit();
    }

    // Fetch current project data
    $projectQuery = $conn->prepare("SELECT * FROM projects WHERE project_id = ?");
    $projectQuery->bind_param("i", $projectId);
    $projectQuery->execute();
    $projectResult = $projectQuery->get_result();
    $currentProject = $projectResult->fetch_assoc();

    // Prepare the SQL update query for the projects table, only if the project details have changed
    if ($currentProject['project_name'] !== $projectName || 
        $currentProject['description'] !== $description || 
        $currentProject['start_date'] !== $startDate || 
        $currentProject['due_date'] !== $dueDate || 
        $currentProject['budget'] !== $budget) {

        $updateProjectQuery = $conn->prepare("UPDATE projects SET project_name = ?, description = ?, start_date = ?, due_date = ?, budget = ? WHERE project_id = ?");
        $updateProjectQuery->bind_param("ssssdi", $projectName, $description, $startDate, $dueDate, $budget, $projectId);
        if (!$updateProjectQuery->execute()) {
            echo "<script>alert('Error updating project details: " . $conn->error . "'); window.location.href='admin_projects.php';</script>";
            exit();
        }
    }

    // Get the current employee assignments
    $assignedEmployeesQuery = $conn->prepare("SELECT employee_id FROM project_employees WHERE project_id = ?");
    $assignedEmployeesQuery->bind_param("i", $projectId);
    $assignedEmployeesQuery->execute();
    $assignedEmployeesResult = $assignedEmployeesQuery->get_result();
    $currentAssignments = [];
    while ($row = $assignedEmployeesResult->fetch_assoc()) {
        $currentAssignments[] = $row['employee_id'];
    }

    // Check for employees that need to be added, removed, or updated
    $employeesToRemove = array_diff($currentAssignments, $employees);
    $employeesToAdd = array_diff($employees, $currentAssignments);

    // Remove employees who are no longer assigned
    foreach ($employeesToRemove as $employeeId) {
        $deleteQuery = $conn->prepare("DELETE FROM project_employees WHERE project_id = ? AND employee_id = ?");
        $deleteQuery->bind_param("ii", $projectId, $employeeId);
        $deleteQuery->execute();
    }

    // Assign new employees or update existing ones
    foreach ($employeesToAdd as $employeeId) {
        $role = isset($projectRoles[$employeeId]) ? $projectRoles[$employeeId] : 'Member';
        $task = isset($tasks[$employeeId]) ? $tasks[$employeeId] : '';

        // Insert new assignments
        $insertQuery = $conn->prepare("INSERT INTO project_employees (project_id, employee_id, projectRole, task) VALUES (?, ?, ?, ?)");
        $insertQuery->bind_param("iiss", $projectId, $employeeId, $role, $task);
        $insertQuery->execute();
    }

    // Update existing employee assignments (role and task changes)
    foreach ($employees as $employeeId) {
        if (in_array($employeeId, $currentAssignments)) {
            $role = isset($projectRoles[$employeeId]) ? $projectRoles[$employeeId] : 'Member';
            $task = isset($tasks[$employeeId]) ? $tasks[$employeeId] : '';

            // Update existing employee assignments
            $updateAssignmentQuery = $conn->prepare("UPDATE project_employees SET projectRole = ?, task = ? WHERE project_id = ? AND employee_id = ?");
            $updateAssignmentQuery->bind_param("ssii", $role, $task, $projectId, $employeeId);
            $updateAssignmentQuery->execute();
        }
    }

    // Redirect or display a success message
    echo "<script>alert('Project details edited successfully.'); window.location.href='manage_project.php';</script>";
    exit();
} else {
    echo "<script>alert('Project ID is missing.'); window.location.href='admin_projects.php';</script>";
}

// Close the database connection
mysqli_close($conn);
?>
