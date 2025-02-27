<?php
include("./cofig.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check Admin Table First
    $stmt = $conn->prepare("SELECT id, email, password, role FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_type'] = 'admin'; // Add user type for redirection

            echo json_encode([
                'status' => 'success',
                'role' => $user['role'],
                'user_type' => 'admin'
                 
            ]);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password!']);
            exit();
        }
    }

    // Check Employee & Project Manager Table
    $stmt = $conn->prepare("SELECT id, email, password, role FROM employees WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Assign user type based on role
            if ($user['role'] == 'project_manager') {
                $_SESSION['user_type'] = 'project_manager';
                $userType = 'project_manager';
            } else {
                $_SESSION['user_type'] = 'employee';
                $userType = 'employee';
            }

            echo json_encode([
                'status' => 'success',
                'role' => $user['role'],
                'user_type' => $userType
            ]);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password!']);
            exit();
        }
    }

    // If no user is found
    echo json_encode(['status' => 'error', 'message' => 'User not found!']);
}
?>
