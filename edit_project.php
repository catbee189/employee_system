<?php 

include("./layouts/header.php");
include("./layouts/sidebar.php");
require "cofig.php";

?>



<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background: #eee !important;
        }

        .wrapper {
            margin-top: 80px;
            margin-bottom: 80px;
        }

        .form-create-project {
            max-width: 500px;
            padding: 15px 35px 45px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-create-project .form-heading {
            margin-bottom: 30px;
            text-align: center;
        }

        .form-control {
            font-size: 16px;
            padding: 10px;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .required {
            color: red;
        }

        .search-bar {
            margin-bottom: 15px;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
    </style>
    <script>
        const holidays = [{
                month: 11,
                day: 1
            }, // All Saints' Day
            {
                month: 11,
                day: 2
            }, // All Souls' Day
            {
                month: 11,
                day: 30
            }, // All Bonifacio Day
            {
                month: 12,
                day: 8
            }, // Feast of the Immaculate Conception
            {
                month: 12,
                day: 24
            }, // Christmas Eve
            {
                month: 12,
                day: 25
            }, // Christmas Day
            {
                month: 12,
                day: 30
            }, // Rizal Day
            {
                month: 12,
                day: 31
            }, // New Year's Eve
            {
                month: 1,
                day: 1
            }, // New Year's Day
            {
                month: 1,
                day: 29
            }, // Lunar New Year's Day
            {
                month: 2,
                day: 25
            }, // People Power Anniversary
            {
                month: 4,
                day: 9
            }, // The Day of Valor
            {
                month: 5,
                day: 1
            }, // Labor Day
            {
                month: 6,
                day: 12
            }, // Independence Day
            {
                month: 8,
                day: 21
            }, // Ninoy Aquino Day
            {
                month: 8,
                day: 25
            } // National Heroes Day
        ];

        function isHoliday(date) {
            const selectedDate = new Date(date);
            const selectedMonth = selectedDate.getMonth() + 1; // getMonth() returns 0 for January
            const selectedDay = selectedDate.getDate();

            return holidays.some(holiday =>
                holiday.month === selectedMonth && holiday.day === selectedDay
            );
        }

        function filterEmployees() {
            const searchInput = document.getElementById('employeeSearchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.employee-row');

            rows.forEach(row => {
                const label = row.querySelector('.employee-name').textContent.toLowerCase();
                row.style.display = label.includes(searchInput) ? '' : 'none';
            });
        }

        function validateDates() {
            const startDate = new Date(document.getElementById('start_date').value);
            const dueDate = new Date(document.getElementById('due_date').value);
            const errorMsg = document.getElementById('dateError');

            if (startDate && dueDate && dueDate < startDate) {
                errorMsg.textContent = 'End date cannot be earlier than start date.';
                return false; // Prevent form submission
            } else {
                errorMsg.textContent = ''; // Clear error message
            }
            return true; // Allow form submission
        }

        function confirmUpdate() {
            return confirm("Are you sure you want to edit this project?");
        }

        function openModal() {
            const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
            modal.show();
        }

        function assignEmployees() {
    console.log("Assign button clicked!");
    const selectedEmployees = [];
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        selectedEmployees.push(checkbox.value);
    });

    console.log("Selected Employees:", selectedEmployees);
    
    const assignedInput = document.getElementById('assignedEmployees');
    if (assignedInput) {
        assignedInput.value = selectedEmployees.join(',');
        console.log("Updated assignedEmployees input:", assignedInput.value);
    } else {
        console.error("Element #assignedEmployees not found!");
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('assignEmployeesModal'));
    if (modal) {
        modal.hide();
    } else {
        console.error("Modal not found!");
    }
}

        // function setMinDate() {
        //     const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
        //     document.getElementById('start_date').setAttribute('min', today);
        // }

        function checkForHolidays() {
            const startDateInput = document.getElementById('start_date');
            const selectedDate = startDateInput.value;

            if (selectedDate && isHoliday(selectedDate)) {
                alert('The selected date is a holiday and cannot be chosen. Please select a different date.');
                startDateInput.value = ''; // Clear the selection
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // setMinDate();
            const employeeModal = document.getElementById('employeeModal');

            employeeModal.addEventListener('hidden.bs.modal', function() {

                document.getElementById('employeeSearchInput').value = '';
                filterEmployees();
            });

            document.getElementById('start_date').addEventListener('change', checkForHolidays);
        });
    </script>
</head>

<body>
    <div class="content">
        <div class="wrapper">
            <form class="form-create-project" action="update_project.php" method="POST" onsubmit="return validateDates() && confirmUpdate()">
                <h2 class="form-heading">Edit Project</h2>

                <?php
                // Include the database configuration file

                // Get the project ID from the URL
                if (isset($_GET['id'])) {
                    $projectId = intval($_GET['id']); // Sanitize input
                } else {
                    echo "<p class='text-danger'>No project ID specified.</p>";
                    exit();
                }

                // Fetch the project data from the database
                $projectQuery = mysqli_query($conn, "SELECT * FROM projects WHERE project_id = $projectId");
                $project = mysqli_fetch_assoc($projectQuery);

                // Check if project exists
                if (!$project) {
                    echo "<p class='text-danger'>Project not found.</p>";
                    exit();
                }

                // Fetch assigned employees
                $assignedEmployeesQuery = mysqli_query($conn, "SELECT employee_id FROM project_employees WHERE project_id = $projectId");
                $assignedEmployees = [];
                while ($row = mysqli_fetch_assoc($assignedEmployeesQuery)) {
                    $assignedEmployees[] = $row['employee_id'];
                }
                ?>

                <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">

                <label for="project_name">Project Name<span class="required">*</span></label>
                <input type="text" class="form-control" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>" required autofocus>

                <label for="description">Project Description</label>
                <textarea class="form-control" name="description"><?php echo htmlspecialchars($project['description']); ?></textarea>

                <label for="start_date">Start Date<span class="required">*</span></label>
                <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo $project['start_date']; ?>" required>

                <label for="due_date">Due Date<span class="required">*</span></label>
                <input type="date" class="form-control" name="due_date" id="due_date" value="<?php echo $project['due_date']; ?>" required>

                <div id="dateError" class="error"></div>

                <label for="budget">Budget (PHP)</label>
                <input type="number" class="form-control" name="budget" value="<?php echo $project['budget']; ?>" placeholder="ex. 1000" step="0.01">

                <label for="employees">Assign Employees</label>
                <br>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignEmployeesModal" onclick="openModal()">Assign Employees</button>

                <!-- Modal for assigning employees -->
                <div class="modal fade" id="assignEmployeesModal" tabindex="-1" aria-labelledby="assignEmployeesModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="assignEmployeesModalLabel">Assign Employees</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="text" id="searchInput" class="form-control search-bar" placeholder="Search employees..." onkeyup="filterEmployees()">
                                <?php
                                // Fetch the existing tasks for employees in this project
                                $existingTasksQuery = mysqli_query($conn, "SELECT employee_id, task FROM project_employees WHERE project_id = $projectId");
                                $existingTasks = [];
                                while ($row = mysqli_fetch_assoc($existingTasksQuery)) {
                                    $existingTasks[$row['employee_id']] = $row['task'];
                                }
                                ?>

                                <table class="table table-striped mt-3">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Employee Name</th>
                                            <th>Employee ID</th>
                                            <th>Department</th>
                                            <th>Project Role</th>
                                            <th>Task</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch employees from the employees table, sorted by employee_name
                                        $result = mysqli_query($conn, "
                                            SELECT e.id, CONCAT(e.firstname, ' ', e.lastname) AS employee_name, e.employeeID, e.department, pe.projectRole ,e.role
                                            FROM employees e
                                            LEFT JOIN project_employees pe ON e.id = pe.employee_id AND pe.project_id = $projectId
                                            ORDER BY employee_name ASC
                                        ");
                                        // Loop through each employee and create a row in the table
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $checked = in_array($row['id'], $assignedEmployees) ? 'checked' : '';
                                            $task = isset($existingTasks[$row['id']]) ? $existingTasks[$row['id']] : ''; // Get existing task if available
                                            echo "<tr class='employee-row'>";
                                            echo "<td><input class='form-check-input employee-checkbox' type='checkbox' name='employees[]' value='" . $row['id'] . "' $checked></td>";
                                            echo "<td class='employee-name'>" . htmlspecialchars($row['employee_name']) . "</td>";
                                            echo "<td class='employee-id'>" . htmlspecialchars($row['employeeID']) . "</td>";
                                            echo "<td class='employee-department'>" . htmlspecialchars($row['department']) . "</td>";
                                            // Project role dropdown
                                            echo "<td style='color: " . 
                                            ($row['role'] == 'Project Manager' ? "blue" : 
                                            ($row['role'] == 'employee' ? "green" : "black")) . 
                                            "; font-weight: bold;'>" . htmlspecialchars($row['role']) . "</td>";
                                                                                    echo "</td>";
                                            echo "<td><input type='text' class='form-control task-input' name='task[" . $row['id'] . "]' value='" . htmlspecialchars($task) . "' placeholder='Enter task'></td>";
                                            echo "</tr>";
                                        }

                                        // Close the connection
                                        mysqli_close($conn);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="assignEmployees();">Assign Employees</button>
                            </div>
                        </div>
                    </div>
                </div>

                <br><br><br>
                <button class="btn btn-lg btn-success btn-block" type="submit" name="update_project">Update Project</button>
                <br><br>
                <a href="admin_projects.php" class="btn btn-lg btn-secondary">Back</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
<?php include("./layouts/footer.php"); ?>
