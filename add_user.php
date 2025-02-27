<?php
include('./cofig.php'); // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data and escape it to prevent SQL injection
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $suffix = mysqli_real_escape_string($conn, $_POST['suffix']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = mysqli_real_escape_string($conn, $_POST['pass']);
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Handle file upload for profile image
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        // A new image is uploaded
        $profileImage = $_FILES['profileImage']['name'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($profileImage);
        
        // Move the uploaded file to the target directory
        move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFile);
    } else {
        // If no image is uploaded, keep the existing image (retrieve the existing image from the database)
        if (isset($_POST['existingProfileImage'])) {
            $profileImage = $_POST['existingProfileImage'];  // Keep the current image
        } else {
            $profileImage = NULL;  // If no image and no existing image, set as NULL
        }
    }

    // Check if email already exists
    $checkEmailQuery = "SELECT * FROM `admin` WHERE email = '$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        // Email already exists
        $response = [
            'status' => 'error',
            'message' => 'Email already exists.'
        ];
    } else {
        // Insert data into the database
        $insertQuery = "INSERT INTO `admin` (lastname, firstname, middlename, suffix, email, role, password,profile_image) 
                        VALUES ('$lastname', '$firstname', '$middlename', '$suffix', '$email', '$role', '$hashed_password', '$profileImage')";
        
        if ($conn->query($insertQuery) === TRUE) {
            // Success message
            $response = [
                'status' => 'success',
                'message' => 'User added successfully!'
            ];
        } else {
            // Error message
            $response = [
                'status' => 'error',
                'message' => 'Error: ' . $conn->error
            ];
        }
    }

    // Return the response as JSON
    echo json_encode($response);
    exit();
}
?>
