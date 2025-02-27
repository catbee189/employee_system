
    <?php 
    include("./layouts/header.php");
include("./layouts/sidebar.php");


include("./cofig.php");

?>
  


      
  <?php



// Initialize variables for project counts
$query = "SELECT COUNT(*) AS total_projects FROM projects";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$total_projects = $row['total_projects'];

$query = "
    SELECT COUNT(*) AS near_due_projects 
    FROM projects 
    WHERE DATEDIFF(due_date, CURDATE()) > 0 
    AND DATEDIFF(due_date, CURDATE()) <= DATEDIFF(due_date, start_date) * 0.3
";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$near_due_projects = $row['near_due_projects'];

$query = "SELECT COUNT(*) AS total_employees FROM employees";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$total_employees = $row['total_employees'];

// Fetch projects and their progress directly from the projects table
$projects = [];
$query = "SELECT * FROM projects ORDER BY due_date ASC";
$result = mysqli_query($conn, $query);

while ($project = mysqli_fetch_assoc($result)) {
    // Fetch the progress from the 'progress' field in the projects table
    $projectProgress = $project['progress'] ?? 0;

    // Add progress data to the project array
    $project['progress'] = $projectProgress;
    $projects[] = $project;
}

// Fetch project data for Gantt chart
$ganttData = [];
$query = "SELECT project_name, start_date, due_date, progress FROM projects ORDER BY due_date ASC";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $ganttData[] = [
        'name' => $row['project_name'],
        'start' => $row['start_date'],
        'end' => $row['due_date'],
        'progress' => $row['progress']
    ];
}

// Prepare data for the Gantt chart
$ganttChartData = json_encode($ganttData);
mysqli_close($conn);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        .card-body {
            height: 185px; /* Ensures all cards have the same height */
        }
        .btn {
            margin-top: 24px;
        }
        .content {
            margin: 20px;
        }
        .gantt-chart-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="content">
        <h2>Welcome to the Project Management System</h2>

        <!-- Overview Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">All Projects</h5>
                        <p class="card-text"><?php echo $total_projects; ?></p>
                        <a href="admin_projects.php" class="btn btn-primary">View Projects</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Near Due Projects</h5>
                        <p class="card-text"><?php echo $near_due_projects; ?></p>
                        <a href="admin_projects.php" class="btn btn-warning">Check Status</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Employees</h5>
                        <p class="card-text"><?php echo $total_employees; ?></p>
                        <a href="admin_manage_emp.php" class="btn btn-info">Manage Employees</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project List Section -->
        <div class="row">
            <div class="col-md-12">
                <h3 class="mt-5">Project List</h3>
                
                <!-- Search Bar -->
                <div class="mb-3">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search for a project...">
                </div>

                <table class="table table-bordered" id="projectTable">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody id="projectResults">
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                            style="width: <?php echo htmlspecialchars($project['progress']); ?>%;" 
                                            aria-valuenow="<?php echo htmlspecialchars($project['progress']); ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo htmlspecialchars($project['progress']); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gantt Chart Section -->
        <div class="row">
            <div class="col-md-12">
                <h3 class="mt-5">Project Timeline (Gantt Chart)</h3>
                <canvas id="ganttChart" style="height: 400px;"></canvas>
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ganttData = <?php echo $ganttChartData; ?>;
    const ctx = document.getElementById('ganttChart').getContext('2d');

    // Prepare data for the bar chart
    const labels = ganttData.map(project => project.name);
    const startDates = ganttData.map(project => new Date(project.start).getTime());
    const endDates = ganttData.map(project => new Date(project.end).getTime());

    // Calculate durations for each project
    const durations = endDates.map((end, index) => (end - startDates[index]) / (1000 * 60 * 60 * 24)); // in days
    const projectStartOffset = startDates.map(start => (start - Math.min(...startDates)) / (1000 * 60 * 60 * 24)); // Offset from earliest start date

    // Chart configuration
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Project Duration (Days)',
                data: durations,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                barPercentage: 0.6,
                categoryPercentage: 0.8,
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal bar chart
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Duration (Days)'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Projects'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Start: ${new Date(ganttData[context.dataIndex].start).toLocaleDateString()} 
                                    End: ${new Date(ganttData[context.dataIndex].end).toLocaleDateString()}`;
                        }
                    }
                }
            }
        }
    });
</script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

<?php
include("./layouts/footer.php");
?>
  