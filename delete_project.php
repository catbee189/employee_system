<?php
// Include the database configuration file
require 'cofig.php';

// Check if a project ID is passed via GET
if (isset($_GET['id'])) {
    $project_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Disable foreign key checks to allow project deletion
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

    // Delete the project from the 'projects' table
    $delete_project = "DELETE FROM projects WHERE project_id = '$project_id'";

    if (mysqli_query($conn, $delete_project)) {
        // Also delete associated records in 'project_employees' table, if any
        $delete_employees = "DELETE FROM project_employees WHERE project_id = '$project_id'";
        mysqli_query($conn, $delete_employees);

        // Enable foreign key checks after deletion
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

        echo "<script>alert('Project deleted successfully'); window.location='manage_project.php';</script>";
    } else {
        echo "Error: " . $delete_project . "<br>" . mysqli_error($conn);
    }

    // Close the connection
    mysqli_close($conn);
} else {
    echo "<script>alert('Invalid request'); window.location='admin_projects.php';</script>";
    exit;
}
?>
