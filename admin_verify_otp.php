<?php
session_start();
require_once 'config.php';

if (isset($_POST['verify'])) {
    $entered_otp  = trim($_POST['otp']          ?? '');
    $new_password = $_POST['new_password']       ?? '';

    // ── FIX: was checking $_SESSION['otp'] but send stored $_SESSION['admin_otp']
    //         Now both use 'admin_otp' consistently.
    if (!isset($_SESSION['admin_otp'], $_SESSION['admin_email'])) {
        echo "<script>alert('Session expired. Please try again.'); window.location='admin_forgot.html';</script>";
        exit();
    }

    if ($entered_otp == $_SESSION['admin_otp']) {
        if (strlen($new_password) < 8) {
            echo "<script>alert('Password must be at least 8 characters.'); history.back();</script>";
            exit();
        }

        $pdo    = getDB();
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt   = $pdo->prepare('UPDATE admin SET password = ? WHERE email = ?');
        $ok     = $stmt->execute([$hashed, $_SESSION['admin_email']]);

        unset($_SESSION['admin_otp'], $_SESSION['admin_email']);

        if ($ok) {
            echo "<script>alert('Password Reset Successful!'); window.location='admin_login.php';</script>";
        } else {
            echo "<script>alert('Update failed. Please try again.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid OTP. Please try again.'); history.back();</script>";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP — CampusCare</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="logo-wrap">
        <img src="dsce_logo.jpg" alt="DSCE Logo">
    </div>
    <h2>Verify OTP</h2>
    <p class="subtitle">Enter the OTP sent to the admin email</p>

    <form method="POST">
        <div class="form-group">
            <label>OTP Code</label>
            <input type="text" name="otp" placeholder="6-digit OTP" required maxlength="6">
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Min. 8 characters" required minlength="8">
        </div>
        <button type="submit" name="verify" class="btn btn-primary">Verify & Reset</button>
    </form>

    <div class="auth-links">
        <a href="admin_login.php">← Back to Login</a>
    </div>
</div>
</body>
</html>
