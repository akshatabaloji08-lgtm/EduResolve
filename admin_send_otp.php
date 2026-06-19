<?php
session_start();
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$username = trim($_POST['username'] ?? '');

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin) {
    echo "<script>alert('Admin username not found.'); history.back();</script>";
    exit();
}

$email = $admin['email'];
$otp   = random_int(100000, 999999);

// ── FIX: store as 'admin_otp' to match admin_verify_otp.php
$_SESSION['admin_otp']   = $otp;
$_SESSION['admin_email'] = $email;

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
    $mail->Subject = 'CampusCare Admin OTP';
    $mail->Body    = "Your Admin OTP is: $otp";
    $mail->send();
    header('Location: admin_verify_otp.php');
    exit();
} catch (Exception $e) {
    echo "<script>alert('OTP sending failed. Please try again.'); history.back();</script>";
    exit();
}
