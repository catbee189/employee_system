<?php
include('config.php');

if (isset($_POST['employeeID'])) {  // Check if the form is being submitted

    $employeeID = $_POST['employeeID'];
    $id = $_POST['id'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $suffix = $_POST['suffix'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Handle file upload
    if ($_FILES['profileImage']['name']) {
        $profileImage = $_FILES['profileImage'];
        $profileImagePath = "employee_profile/" . basename($profileImage['name']);
        if (!move_uploaded_file($profileImage['tmp_name'], $profileImagePath)) {
            echo 'error'; // File upload failed
            exit;
        }
    } else {
        // If no file is uploaded, use the current profile image
        $profileImagePath = $_POST['currentProfileImage']; 
    }

    // SQL query to update employee details
    $sql = "UPDATE employees SET 
            lastname = ?, firstname = ?, middlename = ?, suffix = ?, 
            phone = ?, email = ?, department = ?, role = ?, 
            password = ?, profile_image = ? ,
            employeeID = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Handle query preparation error
        error_log("SQL Error: " . $conn->error);  // Log the error
        echo 'error'; // SQL preparation failed
        exit;
    }

    // Binding the parameters
    $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Hash the password before saving
    $stmt->bind_param('ssssssssssii', $lastname, $firstname, $middlename, $suffix, 
                      $phone, $email, $department, $role, $passwordHash, $profileImagePath, $employeeID, $id);

    // Execute the query
    if ($stmt->execute()) {
        echo 'success'; // Successfully updated the employee
    } else {
        // Handle execution failure
        error_log("Execution Error: " . $stmt->error); // Log the error
        echo 'error'; // SQL execution failed
    }
}
?>
