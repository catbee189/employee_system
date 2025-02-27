<?php
include("./layouts/header.php");
include("./layouts/sidebar.php");
require "cofig.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and sanitize it
    $last_name = $conn->real_escape_string($_POST['lastname']);
    $first_name = $conn->real_escape_string($_POST['firstname']);
    $middle_name = $conn->real_escape_string($_POST['middlename']);
    $suffix = $conn->real_escape_string($_POST['suffix']);

    // Combine into the format: Last name, First name M.
    $employee_name = $first_name . ' ' . $middle_name . ' ' . $last_name . ' ' . $suffix;

    $employeeID = $conn->real_escape_string($_POST['employeeID']);
    $department = $conn->real_escape_string($_POST['department']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $user_role = $conn->real_escape_string($_POST['role']);

    // Generate password (8 characters)
    $password = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', 8)), 0, 8);

    // Check if email exists
    $check_email_query = "SELECT * FROM employees WHERE email = '$email'";
    $email_result = $conn->query($check_email_query);
    if ($email_result->num_rows > 0) {
        echo "
      <script>
          document.addEventListener('DOMContentLoaded', function() {
              var alertModalMessage = document.getElementById('alertModalMessage');
              alertModalMessage.textContent = 'Email address already exists.';
              var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
              alertModal.show();
          });
      </script>";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Unique ID
        $unique_id = rand(time(), 100000000);

        // Handle Profile Image Upload
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
            // A new image is uploaded
            $profileImage = $_FILES['profileImage']['name'];
            $targetDir = "employee_profile/";
            $targetFile = $targetDir . basename($profileImage);
            
            // Move the uploaded file to the target directory
            move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFile);
        } else {
            // If no image is uploaded, set profile image to NULL
            $profileImage = NULL;
        }

        // Insert the employee into the employees table
        $sql = $conn->prepare("INSERT INTO employees (lastname, firstname, middlename, suffix, employee_name, employeeID, unique_id, department, phone, email, password, role, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sql->bind_param("sssssssssssss", $last_name, $first_name, $middle_name, $suffix, $employee_name, $employeeID, $unique_id,  $department, $phone, $email, $hashed_password, $user_role, $profileImage);

        if ($sql->execute()) {
            $employee_id = $conn->insert_id;

            // Send email
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = SMTP::DEBUG_OFF;

            $mail->isSMTP();
            $mail->SMTPAuth = true;

            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->Username = 'hccpms1946@gmail.com';
            $mail->Password = 'xzhk wnln xfzh gemu';

            $mail->setFrom("noreply@gmail.com", "PMS");
            $mail->addAddress($email);

            $mail->isHTML(true);

            $mail->Subject = 'Registration Successful';
            $mail->Body = "Dear $first_name,<br><br>Congratulations! Your account on Project Management System has been successfully created.<br><br>Here are your details:<br><br>Email: $email<br>Password: $password<br>You can now log in and explore our features at PMS.<br><br>If you have any questions or need assistance, feel free to contact us at hccpms1946@gmail.com.<br><br>Thank you for joining us, and welcome aboard!";

            // Disable SSL certificate verification
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            if (!$mail->send()) {
                $error_message = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
                error_log($error_message); // Log error message to server error log
                echo "<script>alert('$error_message');</script>";
                exit;
            } else {
                echo "
              <script>
                  document.addEventListener('DOMContentLoaded', function() {
                      var modalMessage = document.getElementById('modalMessage');
                      modalMessage.textContent = 'Employee successfully added.';
                      var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                      messageModal.show();
                      messageModal._element.addEventListener('hidden.bs.modal', function () {
                          window.location = 'manage_employee.php';
                      });
                  });
              </script>";
            }
        } else {
            echo "
          <script>
              document.addEventListener('DOMContentLoaded', function() {
                  var modalMessage = document.getElementById('modalMessage');
                  modalMessage.textContent = 'Error: " . $conn->error . "'; 
                  var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                  messageModal.show();
              });
          </script>";
        }

        // Close statement
        $sql->close();
    }

    // Close connection
    $conn->close();
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
     .form-register label {
    margin-bottom: 10px;
}

.form-control {
    margin-bottom: 15px;
}

.error {
    color: red;
}

.btn {
    margin-top: 20px;
}

img {
    max-width: 100%;
    height: auto;
}

    </style>
    <script>
        function showAlert(message) {
            const alertModalMessage = document.getElementById('alertModalMessage');
            alertModalMessage.textContent = message; // Set the message in the modal
            const alertModal = new bootstrap.Modal(document.getElementById('alertModal')); // Create a new modal instance
            alertModal.show(); // Show the modal
        }

        function validateEmail() {
            const email = document.getElementById('email').value;
            const errorMsg = document.getElementById('emailError');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email regex pattern

            if (email && !emailPattern.test(email)) {
                errorMsg.textContent = 'Please enter a valid email address.';
                return false; // Prevent form submission
            } else {
                errorMsg.textContent = ''; // Clear error message
            }
            return true; // Allow form submission
        }

        function checkEmailExists() {
            const email = document.getElementById('email').value;

            // Check if the email field is not empty
            if (email) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "check_email.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                // Handle response
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const response = xhr.responseText;

                        if (response === 'exists') {
                            showAlert('Email address already exists.'); // Use the showAlert function
                            document.getElementById('email').value = ''; // Clear the email field
                        }
                    }
                };

                xhr.send("email=" + encodeURIComponent(email));
            }
        }

        // Add event listener to the email input field
        document.getElementById('email').addEventListener('blur', checkEmailExists);

        function validatePhone() {
            const phone = document.getElementById('phone').value;
            const errorMsg = document.getElementById('phoneError');

            // Regular expression to check if phone starts with 9 and has exactly 10 digits
            const phonePattern = /^9\d{9}$/;

            if (phone && !phonePattern.test(phone)) {
                errorMsg.textContent = 'Please enter a valid phone number.';
                return false; // Prevent form submission
            } else {
                errorMsg.textContent = ''; // Clear error message
            }
            return true; // Allow form submission
        }

        function confirmRegister() {
            return new Promise((resolve) => {
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

                document.getElementById('confirmModalMessage').textContent = "Are you sure you want to add this employee?";

                document.getElementById('confirmButton').onclick = function() {
                    confirmModal.hide();
                    resolve(true); // User confirmed
                };

                document.getElementById('cancelButton').onclick = function() {
                    confirmModal.hide();
                    resolve(false); // User canceled
                };

                confirmModal.show();
            });
        }

        document.querySelector('form').onsubmit = async function(event) {
            event.preventDefault(); // Prevent default submission
            if (validatePhone() && validateEmail()) { // Ensure other validations pass
                if (await confirmRegister()) {
                    this.submit(); // Proceed with form submission
                }
            }
        };
    </script>
</head>

<body>
<div class="content">
    <div class="wrapper">
        <form class="form-register" action="" method="POST" onsubmit="return validatePhone() && validateEmail() && confirmRegister()" enctype="multipart/form-data">
            <h2 class="form-register-heading">Add Employee</h2>

            <!-- Row 1: Last name & First name -->
            <div class="row">
                <div class="col-md-6">
                    <label for="lastname">Last name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="lastname" placeholder="ex. Dela Cruz" required autofocus="" onkeypress="return isCharacter(event)" />
                </div>
                <div class="col-md-6">
                    <label for="firstname">First name<span class="required">*</span></label>
                    <input type="text" class="form-control" name="firstname" placeholder="ex. Juan" required autofocus="" onkeypress="return isCharacter(event)" />
                </div>
            </div>

            <!-- Row 2: Middle name & Suffix -->
            <div class="row">
                <div class="col-md-6">
                    <label for="middlename">Middle name</label>
                    <input type="text" class="form-control" name="middlename" placeholder="ex. Mercado" autofocus="" onkeypress="return isCharacter(event)" />
                </div>
                <div class="col-md-6">
                    <label for="suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" placeholder="ex. Jr." />
                </div>
            </div>

            <!-- Row 3: Employee ID & Phone Number -->
            <div class="row">
                <div class="col-md-6">
                    <label for="employeeID">Employee ID<span class="required">*</span></label>
                    <input type="number" class="form-control" name="employeeID" placeholder="ex. 123465" required autofocus="" onkeypress="return isNumberKey(event)" />
                </div>
                <div class="col-md-6">
                    <label for="phone">Phone Number<span class="required">*</span></label>
                    <input type="text" class="form-control" name="phone" id="phone" placeholder="ex. 9123456789" required autofocus="" maxlength="10" onkeypress="return isNumberKey(event)" />
                    <div id="phoneError" class="error"></div>
                </div>
            </div>

            <!-- Row 4: Email & Department -->
            <div class="row">
                <div class="col-md-6">
                    <label for="email">Email Address<span class="required">*</span></label>
                    <input type="text" class="form-control" name="email" id="email" placeholder="ex. sample@gmail.com" required autofocus="" onblur="checkEmailExists()" />
                    <div id="emailError" class="error"></div>
                </div>
                <div class="col-md-6">
                    <label for="department">Department<span class="required">*</span></label>
                    <select class="form-control" placeholder="Select Department" id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="College">College</option>
                        <option value="Basic Ed">Basic Ed</option>
                        <option value="Non-Teaching">Non-Teaching</option>
                    </select>
                </div>
            </div>

            <!-- Row 5: Role & Profile Picture -->
            <div class="row">
                <div class="col-md-6">
                    <label for="role">Role<span class="required">*</span></label>
                    <select class="form-control" name="role" id="role" required>
                        <option value="">Select Role</option>
                        <option value="employee">Employee</option>
                        <option value="project manager">Project Manager</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="profile_image">Profile Picture</label>
                    <input type="file" class="form-control" name="profileImage" id="profile_image" accept="image/*" onchange="previewImage(event)" />
                    <br>
                    <img id="imagePreview" src="#" alt="Image Preview" class="img-fluid" style="display: none; max-height: 200px;">
                    </div>
            </div>

            <!-- Row 6: Password Field (Not in the original, but added for structure) -->
            <div class="row">
                <div class="col-md-12">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" required />
                </div>
            </div>

            <button class="btn btn-lg btn-success btn-block" type="submit" name="register">Add</button>
            <br><br>
            <a href="manage_employee.php" class="btn btn-lg btn-secondary">Back</a>
        </form>
    </div>
</div>
    <?php include "modals.html"; ?>
    <script>
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            // Allow only numbers (keycodes for 0-9)
            if (charCode < 48 || charCode > 57) {
                return false; // Prevent default action for non-numeric keys
            }
            return true; // Allow default action for numeric keys
        }

        function isCharacter(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            // Allow only letters (keycodes for a-z and A-Z) and space bar (keycode 32)
            if ((charCode < 48 || charCode > 57) &&
                (charCode < 65 || charCode > 90) &&
                (charCode < 97 || charCode > 122) &&
                charCode !== 32) { // Check for space bar
                return false; // Prevent default action for non-letter and non-space keys
            }
            return true; // Allow default action for letter keys and space bar
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
<script>
    // Preview image function
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
</html>

<?php 
include("./layouts/footer.php");
?>
