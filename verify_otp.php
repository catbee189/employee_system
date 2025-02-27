<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <style>
        @import "bourbon";

body {
	background: #eee !important;	
}

.wrapper {	
	margin-top: 80px;
  margin-bottom: 80px;
}

.form-signin {
  max-width: 380px;
  padding: 15px 35px 45px;
  margin: 0 auto;
  background-color: #fff;
  border: 1px solid rgba(0,0,0,0.1);  

  .form-signin-heading,
	.checkbox {
    text-align: center;
    margin-bottom: 15px;
	}

	.checkbox {
	  font-weight: normal;
	}

	.form-control {
	  position: relative;
	  font-size: 16px;
	  height: auto;
	  padding: 10px;
		@include box-sizing(border-box);

		&:focus {
		  z-index: 2;
		}
	}

	input[type="number"] {
	  margin-bottom: 20px;
	}

    input[type="number"]::-webkit-inner-spin-button{
      -webkit-appearance: none;
	}
}

    </style>
  <body>
    <div class="wrapper">
      <form class="form-signin" action="verify_otp.php" method="POST">
        <h2 class="form-signin-heading">Verify OTP</h2>
        <input type="number" class="form-control" name="otp" placeholder="Enter OTP" required autofocus />
        <button class="btn btn-lg btn-primary btn-block" type="submit" name="verify_otp">Verify OTP</button>
        <br>
        <a href="forgot_pass.html" style="text-decoration: none;">Back</a>  
      </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php
session_start();
require 'cofig.php';

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['email'];

    // Check if the email and OTP exist in the view
    $sql = "SELECT role, otp, otp_expiry FROM users_login_view WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $role = $row['role'];
        $otp_in_db = $row['otp'];
        $otp_expiry = $row['otp_expiry'];

        // Check if OTP matches and if it has not expired
        if ($entered_otp == $otp_in_db) {
            if (strtotime($otp_expiry) > time()) { // Check if OTP is still valid
                if ($role == 'superadmin') {
                    $sql = "SELECT email FROM superadmin WHERE otp = ?";
                } elseif ($role == 'admin') {
                    $sql = "SELECT email FROM admin WHERE otp = ?";
                } elseif ($role == 'employee') {
                    $sql = "SELECT email FROM employees WHERE otp = ?";
                }

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $otp_in_db);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if($row){
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                
                echo "<script>alert('OTP verified.'); window.location='reset_password.php';</script>";
                
                // OPTIONAL: Invalidate OTP after it's used (make it one-time use)
                if ($role == 'superadmin') {
                    $sql = "UPDATE superadmin SET otp=NULL, otp_expiry=NULL WHERE email=?";
                } elseif ($role == 'admin') {
                    $sql = "UPDATE admin SET otp=NULL, otp_expiry=NULL WHERE email=?";
                } else {
                    $sql = "UPDATE employees SET otp=NULL, otp_expiry=NULL WHERE email=?";
                }
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
            }

            } else {
                // OTP expired
                echo "<script>alert('Invalid OTP. OTP has expired.'); window.location='verify_otp.php';</script>";
            }
        } else {
            // Incorrect OTP
            echo "<script>alert('Invalid OTP.'); window.location='verify_otp.php';</script>";
        }
    } else {
        echo "<script>alert('Something went wrong. Please try again.'); window.location='forgot_pass.html';</script>";
    }
}
?>
