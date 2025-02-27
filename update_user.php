<?php
include('./cofig.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data and escape it to prevent SQL injection
    $id = $_POST['id'];
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $suffix = mysqli_real_escape_string($conn, $_POST['suffix']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = mysqli_real_escape_string($conn, $_POST['pass']);
    
    // Hash the password if changed
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // Keep the existing password if not updated
        $hashed_password = NULL;
    }

    // Handle file upload for profile image
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        // A new image is uploaded
        $profileImage = $_FILES['profileImage']['name'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($profileImage);
        
        // Move the uploaded file to the target directory
        move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFile);
    } else {
        // If no image is uploaded, retain the old profile image
        if (isset($_POST['existingProfileImage'])) {
            $profileImage = $_POST['existingProfileImage']; // Keep the current image
        } else {
            $profileImage = NULL; // Set as NULL if no image and no existing image
        }
    }

    // Update query
    $updateQuery = "UPDATE `admin` SET 
                    lastname = '$lastname', 
                    firstname = '$firstname', 
                    middlename = '$middlename', 
                    suffix = '$suffix', 
                    email = '$email', 
                    role = '$role', 
                    password = '$hashed_password',
                    profile_image = '$profileImage'
                    WHERE id = '$id'";

    if ($conn->query($updateQuery) === TRUE) {
        $response = [
            'status' => 'success',
            'message' => 'User updated successfully!'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error: ' . $conn->error
        ];
    }

    // Return the response as JSON
    echo json_encode($response);
    exit();
}
?>
