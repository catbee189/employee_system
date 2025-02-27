

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
    <title>Create Project</title>
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
            border: 1px solid rgba(0,0,0,0.1);
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

        .error {
            color: red;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .btn {
            width: ;
        }
    </style>
    <script>
        const holidays = [
            { month: 11, day: 1, name: "All Saints' Day" },
            { month: 11, day: 2, name: "All Souls' Day" },
            { month: 11, day: 30, name: "Bonifacio Day" },
            { month: 12, day: 8, name: "Feast of the Immaculate Conception" },
            { month: 12, day: 24, name: "Christmas Eve" },
            { month: 12, day: 25, name: "Christmas Day" },
            { month: 12, day: 30, name: "Rizal Day" },
            { month: 12, day: 31, name: "New Year's Eve" },
            { month: 1, day: 1, name: "New Year's Day" },
            { month: 1, day: 29, name: "Lunar New Year's Day" },
            { month: 2, day: 25, name: "People Power Anniversary" },
            { month: 4, day: 9, name: "The Day of Valor" },
            { month: 5, day: 1, name: "Labor Day" },
            { month: 6, day: 12, name: "Independence Day" },
            { month: 8, day: 21, name: "Ninoy Aquino Day" },
            { month: 8, day: 25, name: "National Heroes Day" }
        ];

        function isHoliday(date) {
            const [year, month, day] = date.split('-').map(Number); // Split the date string
            const holiday = holidays.find(holiday => 
                holiday.month === month && holiday.day === day
            );
            
            return holiday ? holiday.name : null; // Return the name of the holiday, or null if not found
        }

        function filterEmployees() {
            const searchInput = document.getElementById('employeeSearchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.employee-row');

            rows.forEach(row => {
                const nameLabel = row.querySelector('.employee-name').textContent.toLowerCase();
                const idLabel = row.querySelector('.employee-id').textContent.toLowerCase();
                const departmentLabel = row.querySelector('.employee-department').textContent.toLowerCase();

                // Show the row if any of the columns contain the search input
                row.style.display = 
                    nameLabel.includes(searchInput) || 
                    idLabel.includes(searchInput) || 
                    departmentLabel.includes(searchInput) ? '' : 'none';
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

        function confirmCreate() {
            return confirm("Are you sure you want to create this project?");
        }

        function openModal() {
            const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
            modal.show();
        }

        function assignEmployees() {
    const selectedEmployees = [];
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');

    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr'); // Get the row of the selected employee
        const employeeId = checkbox.value;
        const role = row.querySelector('.employee-role').textContent.trim(); // Get role from text inside <td>

        selectedEmployees.push({ id: employeeId, role });
    });

    // Store selected employees as JSON in the hidden input field
    document.getElementById('assignedEmployees').value = JSON.stringify(selectedEmployees);

    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
    modal.hide();
}

        function clearCheckboxes() {
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }
        
        function setMinDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('min', today);
        }

        function checkForHolidays() {
            const startDateInput = document.getElementById('start_date');
            const selectedDate = startDateInput.value;

            const holidayName = isHoliday(selectedDate);

            if (holidayName) {
                // Set the holiday message in the modal
                document.getElementById('holidayMessage').textContent = `The selected date is a holiday: ${holidayName}. Please select a different date.`;

                // Show the modal
                const holidayModal = new bootstrap.Modal(document.getElementById('holidayModal'));
                holidayModal.show();

                // Clear the date field after showing the modal
                startDateInput.value = '';
            } else {
                startDateInput.classList.remove('is-invalid');
            }
        }

            document.addEventListener('DOMContentLoaded', function () {
                setMinDate();
                const employeeModal = document.getElementById('employeeModal');

                employeeModal.addEventListener('hidden.bs.modal', function () {

                    document.getElementById('employeeSearchInput').value = '';
                    filterEmployees();
                });

                const startDateInput = document.getElementById('start_date');
                startDateInput.addEventListener('change', checkForHolidays);
            });
    </script>
</head>
<body>

<div class="content">
    <div class="wrapper">
        <form class="form-create-project" action="create_project.php" method="POST" onsubmit="return validateDates() && confirmCreate()">
            <h2 class="form-heading">Create Project</h2>

            <label for="project_name">Project Name<span class="required">*</span></label>
            <input type="text" class="form-control" name="project_name" placeholder="" required autofocus>

            <label for="description">Project Description</label>
            <textarea class="form-control" name="description" placeholder=""></textarea>

            <label for="start_date">Start Date<span class="required">*</span></label>
            <input type="date" class="form-control" name="start_date" id="start_date" required>

            <label for="due_date">Due Date<span class="required">*</span></label>
            <input type="date" class="form-control" name="due_date" id="due_date" required>

            <div id="dateError" class="error"></div>

            <label for="budget">Budget (PHP)</label>
            <input type="number" class="form-control" name="budget" placeholder="ex. 1000" step="0.01">

            <input type="hidden" name="assigned_employees" id="assignedEmployees">
            <button type="button" class="btn btn-lg btn-primary" onclick="openModal()">Select Employees</button>

            <br><br><br>
            <button class="btn btn-lg btn-success btn-block" type="submit" name="create_project">Create Project</button>

            <br><br>
            <a href="admin_projects.php" class="btn btn-lg btn-secondary">Back</a>
        </form>        
    </div>

    <!-- Employee Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Select Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="employeeSearchInput" class="form-control" placeholder="Search employees..." onkeyup="filterEmployees()">
                    <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Employee Name</th>
                                <th>Employee ID</th>
                                <th>Department</th>
                                <th>Project Role</th>
                                <!-- <th>Task</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Include the database configuration file
                            include('cofig.php');

                            // Fetch employees from the employees table, sorted by employee_name
                            $result = mysqli_query($conn, "SELECT id, employee_name, employeeID, department,role FROM employees ORDER BY employee_name ASC");

                            // Loop through each employee and create a row in the table
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr class='employee-row'>";
                                echo "<td><input class='form-check-input employee-checkbox' type='checkbox' value='" . $row['id'] . "'></td>";
                                echo "<td class='employee-name'>" . $row['employee_name'] . "</td>";
                                echo "<td class='employee-id'>" . $row['employeeID'] . "</td>";
                                echo "<td class='employee-department'>" . $row['department'] . "</td>";
                                // Project role dropdown
                                echo "<td class='employee-role' style='color: " . 
    ($row['role'] == 'Project Manager' ? "blue" : 
    ($row['role'] == 'employee' ? "green" : "black")) . 
    "; font-weight: bold;'>" . htmlspecialchars($row['role']) . "</td>";
                                 echo "<td>";
                                 echo "<input type='text' class='form-control' name='task[" . $row['id'] . "]' placeholder='Assign task here'>";
                                 echo "</td>";
                                echo "</tr>";
                            }

                            // Close the connection
                            mysqli_close($conn);
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="assignEmployees();">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="holidayModalLabel">Holiday Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- This is where the holiday name will be displayed -->
        <p id="holidayMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php 
include("./layouts/footer.php");
?>