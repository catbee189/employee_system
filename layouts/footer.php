<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
          background: #f8f9fa;
        }

        .wrapper {
          margin-top: 80px;
          margin-bottom: 80px;
        }

        .dashboard-container {
          display: flex;
          margin-top: 80px;
        }

        .sidebar {
          width: 250px;
          background-color: white;
          height: 100vh;
          position: fixed;
          padding: 20px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          margin-top: -80px;
          padding-top: 80px;
        }

        .sidebar a {
          color: black;
          display: block;
          padding: 10px;
          text-decoration: none;
        }

        .sidebar a:hover {
          background-color: #023B87;
          color: white;
        }

        .content {
          margin-left: 250px;
          padding: 20px;
          flex-grow: 1;
        }

        .navbar {
          position: fixed;
          top: 0;
          width: 100%;
          z-index: 1030;
        }

        .card {
          margin-top: 20px;
        }

        .d-lg-none {
            margin-top: 30px;
        }

        @media (max-width: 768px) {
          .sidebar {
              display: none;
          }

          .content {
              margin-left: 0;
          }

          .dashboard-container {
              flex-direction: column;
          }
        }
    </style>
  </head>
  <body></body>