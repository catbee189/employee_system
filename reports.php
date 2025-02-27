<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");


// Fetch data for 12 months (0-based for compatibility with JavaScript)
$monthlyData = [
    'created' => array_fill(0, 12, 0),
    'completed' => array_fill(0, 12, 0),
    'unfinished' => array_fill(0, 12, 0)
];

// Created Projects
$query = "SELECT MONTH(created_at) AS month, COUNT(*) AS count 
          FROM projects 
          WHERE start_date IS NOT NULL 
          GROUP BY MONTH(created_at)";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $monthlyData['created'][(int)$row['month'] - 1] = $row['count']; // Adjust to 0-based index
}

// Completed Projects
$query = "SELECT MONTH(completed_at) AS month, COUNT(*) AS count 
          FROM projects 
          WHERE status = 'Completed' AND due_date IS NOT NULL 
          GROUP BY MONTH(completed_at)";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $monthlyData['completed'][(int)$row['month'] - 1] = $row['count'];
}

// Unfinished Projects
$query = "SELECT MONTH(due_date) AS month, COUNT(*) AS count 
          FROM projects 
          WHERE status != 'Completed' AND due_date < CURDATE() AND due_date IS NOT NULL 
          GROUP BY MONTH(due_date)";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $monthlyData['unfinished'][(int)$row['month'] - 1] = $row['count'];
}

// Convert data to JSON for JavaScript
$monthlyDataJSON = json_encode($monthlyData);
mysqli_close($conn);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .chart-container {
            width: 80%;
            margin: 20px auto;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="text-center mb-4">Project Reports for the Year</h2>

    <!-- Created Projects Chart -->
    <div class="chart-container">
        <canvas id="createdProjectsChart"></canvas>
    </div>

    <!-- Completed Projects Chart -->
    <div class="chart-container">
        <canvas id="completedProjectsChart"></canvas>
    </div>

    <!-- Unfinished Projects Chart -->
    <div class="chart-container">
        <canvas id="unfinishedProjectsChart"></canvas>
    </div>
</div>

<script>
    // Monthly Data from PHP
    const monthlyData = <?php echo $monthlyDataJSON; ?>;

// Labels for X-Axis
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Chart.js Configuration
const createBarChart = (canvasId, title, data, backgroundColor) => {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: backgroundColor,
                borderColor: backgroundColor.map(color => color.replace(/0\.6/, '1')), // Ensure border opacity is solid
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: title
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Projects'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Months'
                    }
                }
            }
        }
    });
};

// Render Charts
document.addEventListener('DOMContentLoaded', () => {
    createBarChart(
        'createdProjectsChart',
        'Created Projects',
        monthlyData.created,
        ['rgba(54, 162, 235, 0.6)'] // Blue
    );

    createBarChart(
        'completedProjectsChart',
        'Completed Projects',
        monthlyData.completed,
        ['rgba(75, 192, 192, 0.6)'] // Green
    );

    createBarChart(
        'unfinishedProjectsChart',
        'Unfinished Projects',
        monthlyData.unfinished,
        ['rgba(255, 99, 132, 0.6)'] // Red
    );
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

<?php
include("./layouts/footer.php");
?>