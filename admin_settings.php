<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
require 'cofig.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .card-title {
          font-weight: bold;
        }

        .task-list {
          list-style: none;
          padding-left: 0;
        }

        .task-list li {
          padding: 10px;
          background-color: #f8f9fa;
          border: 1px solid #dee2e6;
          border-radius: 5px;
          margin-bottom: 10px;
        }

        .task-list li .task-title {
          font-weight: bold;
        }

        .task-list li .task-status {
          float: right;
          font-size: 0.9rem;
        }

        .status-completed {
            background-color: #28a745; /* Green */
        }

        .status-in-progress {
            background-color: #ffc107; /* Yellow */
        }

        .status-pending {
            background-color: #dc3545; /* Red */
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .hover-card {
            transition: transform 0.3s, box-shadow 0.3s; /* Smooth transition for transform and shadow */
        }

        .hover-card:hover {
            transform: translateY(-5px); /* Slightly raise the card */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Add shadow on hover */
        }
    </style>
  </head>

  <body>
    <div class="content">
        <h2>Settings</h2>
      <div class="card">
      <div class="card-body">
        <ul class='task-list'>

          <span class='task-title'><a href='change_password.php?id=" . $row['id'] . "' class='btn btn-primary btn-edit'>Change Password</a></span>

        </ul>
      </div>
      </div>
      </div>
  
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>

<?php include("./layouts/footer.php"); ?>