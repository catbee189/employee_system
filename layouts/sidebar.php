<?php
; // Start session

// Restrict access to only super_admin and admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') && $_SESSION['role'] !== 'employee'&& $_SESSION['role'] !== 'project manager') {
    header("Location: login.php"); // Redirect to login if not allowed
    exit();
}

include("./cofig.php"); // Ensure the file name is correct
?>

<?php if (isset($_SESSION['role'])): ?>
<div class="dashboard-container">
  <!-- Sidebar for desktop -->
  <div class="sidebar d-none d-lg-block">
    <h4><i class="fas fa-bars"></i> Menu</h4>
    <?php if ($_SESSION['role'] === 'project manager'): ?>

<a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
<a href="view_project.php"><i class="fas fa-tasks"></i> Projects</a>
<a href="admin_communication.php"><i class="fas fa-comments"></i> Communication</a>
<a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>

<?php endif; ?>
    <?php if ($_SESSION['role'] === 'employee'): ?>

<a href="employee_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
<a href="manage_project.php"><i class="fas fa-tasks"></i> Projects</a>
<a href="admin_communication.php"><i class="fas fa-comments"></i> Communication</a>
<a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>

<?php endif; ?>
    <?php if ($_SESSION['role'] === 'admin'): ?>

    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_project.php"><i class="fas fa-tasks"></i> Projects</a>
    <a href="admin_communication.php"><i class="fas fa-comments"></i> Communication</a>
    <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
    <a href="manage_employee.php"><i class="fas fa-users"></i> Manage Employees</a>

    <?php endif; ?>
    <?php if ($_SESSION['role'] === 'super_admin'): ?>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_project.php"><i class="fas fa-tasks"></i> Projects</a>
    <a href="admin_communication.php"><i class="fas fa-comments"></i> Communication</a>
    <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
      <a href="manage_employee.php"><i class="fas fa-users"></i> Manage Employees</a>
      <a href="manage_admin.php"><i class="fas fa-user-cog"></i> Manage Users</a>
      <a href="manage_logs.php"><i class="fas fa-file-alt"></i> Logs</a>

   
      <?php endif; ?>

  </div>
</div>
<?php endif; ?>
