<?php
session_start();
require 'cofig.php'; // Database connection
require 'vendor/autoload.php'; // Load PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['send_otp'])) {
    $email = $_POST['email'];

    // Check if the email exists in the view
    $sql = "SELECT * FROM users_login_view WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $otp = rand(100000, 999999); // Generate a 6-digit OTP
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Set expiration time for 30 minutes from now
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        // Determine which table the user belongs to (superadmin, admin, or employee)
        $row = $result->fetch_assoc();
        $role = $row['role'];

        // Update the OTP and OTP expiry in the respective table
        if ($role == 'superadmin') {
            $sql = "UPDATE superadmin SET otp=?, otp_expiry=? WHERE email=?";
        } elseif ($role == 'admin') {
            $sql = "UPDATE admin SET otp=?, otp_expiry=? WHERE email=?";
        } else {
            $sql = "UPDATE employees SET otp=?, otp_expiry=? WHERE email=?";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $otp, $otp_expiry, $email);
        $stmt->execute();

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPAuth   = true;

            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->Username   = 'hccpms1946@gmail.com';
            $mail->Password   = 'xzhk wnln xfzh gemu';

            $mail->setFrom("noreply@gmail.com", "PMS");
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'OTP Code';
            $mail->Body    = "To proceed with your request, please use the following One-Time Password (OTP): <b>$otp</b><br><br>This code is valid for 30 minutes. Please do not share this code with anyone for your security.<br><br>If you did not request this, please contact us immediately at hccpms1946@gmail.com.";

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->send();

            echo "<script>alert('OTP has been sent to your email.'); window.location='verify_otp.php';</script>";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('Invalid Email Address.'); window.location='forgot_pass.html';</script>";
    }
}
