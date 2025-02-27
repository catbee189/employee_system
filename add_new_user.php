<?php

include("./layouts/header.php");
include("./layouts/sidebar.php");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h2 class="mb-0">Add User</h2>
                    </div>
                    <div class="card-body">
                        <form id="userForm" action="add_user.php" method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lastname" placeholder="e.g., Dela Cruz" >
                                </div>
                                <div class="col-md-6">
                                    <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="firstname" placeholder="e.g., Juan" >
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="middlename" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middlename" placeholder="e.g., Mercado">
                                </div>
                                <div class="col-md-6">
                                    <label for="suffix" class="form-label">Suffix</label>
                                    <input type="text" class="form-control" name="suffix" placeholder="e.g., Jr.">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address<span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="e.g., sample@gmail.com" >
                                    <div id="emailError" class="text-danger"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                                    <select class="form-select" name="role" >
                                        <option value="" selected disabled>Select role</option>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pass" class="form-label">Password<span class="text-danger">*</span></label>
                                    <input type="password" name="pass" id="pass" class="form-control" >
                                </div>
                                <div class="col-md-6">
                                    <label for="profileImage" class="form-label">Profile Image</label>
                                    <input class="form-control" type="file" id="profileImage" name="profileImage" accept="image/*" onchange="previewImage(event)">
                                </div>
                                <div class="mb-3">
                                    <img id="imagePreview" src="#" alt="Image Preview" class="img-fluid" style="display: none; max-height: 200px;">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Add</button>
                        </form>
                        <a href="./manage_admin.php" class="btn btn-secondary w-100 mt-3">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                var formData = new FormData(this); // Form data including the file

                $.ajax({
                    type: 'POST',
                    url: 'add_user.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            }).then(() => {
                                window.location.href = 'manage_admin.php'; // Redirect after success
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.',
                        });
                    }
                });
            });
        });

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
</body>
</html>
<?php 
include("./layouts/footer.php");
?>