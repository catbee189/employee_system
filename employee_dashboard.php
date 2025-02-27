<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
require 'cofig.php';


$logged_in_user_id = $_SESSION['user_id'];
$query = "SELECT COUNT(*) AS ongoing_projects 
          FROM project_employees pe
          JOIN projects p ON pe.project_id = p.project_id
          WHERE pe.employee_id = $logged_in_user_id
          AND p.status = 'Ongoing'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$ongoing_projects = $row['ongoing_projects'];

// Projects 30% before the due date
$query = "SELECT COUNT(*) AS upcoming_projects 
          FROM project_employees pe
          JOIN projects p ON pe.project_id = p.project_id
          WHERE pe.employee_id = $logged_in_user_id
          AND p.due_date >= CURDATE() 
          AND p.due_date <= DATE_ADD(CURDATE(), INTERVAL (TIMESTAMPDIFF(DAY, p.start_date, p.due_date) * 0.30) DAY)";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$upcoming_projects = $row['upcoming_projects'];

// Completed projects (projects that are marked as completed)
$query = "SELECT COUNT(*) AS completed_projects 
          FROM project_employees pe
          JOIN projects p ON pe.project_id = p.project_id
          WHERE pe.employee_id = $logged_in_user_id
          AND p.status = 'Completed'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$completed_projects = $row['completed_projects'];

mysqli_close($conn)
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      .card-body {
          height: 185px; /* Ensures all cards have the same height */
      }

      .btn {
        margin-top: 24px;
      }
    </style>
  </head>
  <body>

      <!-- Content Area -->
      <div class="content">
        

        <!-- Overview Cards -->
        <div class="row">
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Active Projects</h5>
                <p class="card-text"><?php echo $ongoing_projects?></p>
                <a href="employee_projects.php" class="btn btn-primary">View</a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Upcoming Due Dates</h5>
                <p class="card-text"><?php echo $upcoming_projects?></p>
                <a href="employee_projects.php" class="btn btn-warning">View</a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Completed </h5>
                <p class="card-text"><?php echo $completed_projects?></p>
                <a href="employee_reprots.php" class="btn btn-info">View</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Task List Section -->
        <div class="row">
          <div class="col-md-12">
            <h3 class="mt-5">My Tasks</h3>
            <ul class="task-list">
              <li>
                <span class="task-title">Design Landing Page</span>
                <span class="task-status text-success">In Progress</span>
              </li>
              <li>
                <span class="task-title">Fix Backend API</span>
                <span class="task-status text-danger">Passed Due</span>
              </li>
              <li>
                <span class="task-title">Prepare Project Report</span>
                <span class="task-status text-warning">Due Tomorrow</span>
              </li>
              <li>
                <span class="task-title">Client Feedback Implementation</span>
                <span class="task-status text-primary">Not Started</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Timeline or Calendar Section (Future Extension) -->
        <div class="row">
          <div class="col-md-12">
            <h3 class="mt-5">Project Timeline</h3>
            <p>View project milestones, task deadlines, and more in this section.</p>
            <!-- A calendar or timeline view can be added here -->
            <div class="card">
              <div class="card-body">
                <p>Future extension for timeline visualization or calendar.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
<?php include("./layouts/footer.php"); ?>
