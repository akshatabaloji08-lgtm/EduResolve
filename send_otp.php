<?php
session_start();
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email.');
}

$otp = random_int(100000, 999999);
$_SESSION['otp']   = $otp;
$_SESSION['email'] = $email;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = SMTP_PORT;
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($email);
    $mail->Subject = 'CampusCare — OTP Verification';
    $mail->Body    = "Your OTP is: $otp";
    $mail->send();
    header('Location: verify_otp.html?sent=1');
    exit();
} catch (Exception $e) {
    header('Location: forgot.html?error=send_failed');
    exit();
}
