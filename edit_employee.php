<?php
require 'cofig.php';
include("./layouts/header.php");
include("./layouts/sidebar.php");

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);

    // Fetch employee details using the primary key (id)
    $result = $conn->query("SELECT * FROM employees WHERE id = '$id'");

    if ($result->num_rows == 1) {
        $employee = $result->fetch_assoc();
    } else {
        echo "<script>alert('Employee not found'); window.location='admin_manage_emp.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid request'); window.location='admin_manage_emp.php';</script>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $middlename = $conn->real_escape_string($_POST['middlename']);
    $suffix = $conn->real_escape_string($_POST['suffix']);
    $employeeID = $conn->real_escape_string($_POST['employeeID']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);

    // Handle profile image upload
    if (!empty($_FILES['profileImage']['name'])) {
        $target_dir = "employee_profile/";
        $profileImage = basename($_FILES["profileImage"]["name"]);
        $target_file = $target_dir . $profileImage;

        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
            $updateImage = ", profile_image='$profileImage'";
        } else {
            $updateImage = "";
        }
    } else {
        $updateImage = "";
    }

    // Update query
    $sql = "UPDATE employees SET 
            lastname='$lastname', firstname='$firstname', middlename='$middlename', suffix='$suffix',
            employeeID='$employeeID', phone='$phone', email='$email', department='$department',
            role='$role', password='$password' $updateImage 
            WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated Successfully!',
                        text: 'Employee details have been updated.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location = 'manage_employee.php';
                    });
                }, 500);
              </script>";
    } else {
        echo "<script>
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }, 500);
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="content">
        <div class="wrapper">
            <form class="form-register" id="editEmployeeForm" method="POST" enctype="multipart/form-data">
                <h2 class="form-register-heading">Edit Employee</h2>

                <!-- Last name & First name -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="lastname">Last name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="lastname" value="<?= $employee['lastname']; ?>" required />
                    </div>
                    <div class="col-md-6">
                        <label for="firstname">First name<span class="required">*</span></label>
                        <input type="text" class="form-control" name="firstname" value="<?= $employee['firstname']; ?>" required />
                    </div>
                </div>

                <!-- Middle name & Suffix -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="middlename">Middle name</label>
                        <input type="text" class="form-control" name="middlename" value="<?= $employee['middlename']; ?>" />
                    </div>
                    <div class="col-md-6">
                        <label for="suffix">Suffix</label>
                        <input type="text" class="form-control" name="suffix" value="<?= $employee['suffix']; ?>" />
                    </div>
                </div>

                <!-- Employee ID & Phone Number -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="employeeID">Employee ID<span class="required">*</span></label>
                        <input type="number" class="form-control" name="employeeID" value="<?= $employee['employeeID']; ?>" required />
                    </div>
                    <div class="col-md-6">
                        <label for="phone">Phone Number<span class="required">*</span></label>
                        <input type="text" class="form-control" name="phone" value="<?= $employee['phone']; ?>" required maxlength="10" />
                    </div>
                </div>

                <!-- Email & Department -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="email">Email Address<span class="required">*</span></label>
                        <input type="email" class="form-control" name="email" value="<?= $employee['email']; ?>" required />
                    </div>
                    <div class="col-md-6">
                        <label for="department">Department<span class="required">*</span></label>
                        <select class="form-control" name="department" required>
                            <option value="<?= $employee['department']; ?>"><?= $employee['department']; ?></option>
                            <option value="College">College</option>
                            <option value="Basic Ed">Basic Ed</option>
                            <option value="Non-Teaching">Non-Teaching</option>
                        </select>
                    </div>
                </div>

                <!-- Role & Profile Picture -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="role">Role<span class="required">*</span></label>
                        <select class="form-control" name="role" required>
                            <option value="<?= $employee['role']; ?>"><?= $employee['role']; ?></option>
                            <option value="employee">Employee</option>
                            <option value="project manager">Project Manager</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="profile_image">Profile Picture</label>
                        <input type="file" class="form-control" name="profileImage" accept="image/*" />
                        <img src="employee_profile/<?= $employee['profile_image']; ?>" alt="Image Preview" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="row">
                    <div class="col-md-12">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="pass" class="form-control" >
                        <input type="checkbox" onclick="togglePassword()"> Show Password
                    </div>
                </div>

                <button class="btn btn-lg btn-success btn-block" type="submit" name="update">Update</button>
                <br><br>
                <a href="manage_employee.php" class="btn btn-lg btn-secondary">Back</a>
            </form>
        </div>
    </div>
    <script>
          function previewImage(event) {
            var input = event.target;
            var reader = new FileReader();
            reader.onload = function() {
                var dataURL = reader.result;
                var imagePreview = document.getElementById('imagePreview');
                imagePreview.src = dataURL;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    </script>

<?php include("./layouts/footer.php"); ?>
