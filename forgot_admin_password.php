<?php
session_start();
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message     = '';
$messageType = '';
$step        = $_SESSION['afp_step'] ?? 1;

// ── STEP 1: Send OTP ─────────────────────────────────────────
if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email !== ADMIN_EMAIL) {
        $message     = 'Admin email not recognised.';
        $messageType = 'danger';
    } else {
        $otp = random_int(100000, 999999);
        $_SESSION['afp_otp']   = $otp;
        $_SESSION['afp_email'] = $email;
        $_SESSION['afp_step']  = 2;
        $step = 2;

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
            $mail->isHTML(true);
            $mail->Subject = 'CampusCare Admin — Password Reset OTP';
            $mail->Body    = "
                <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;background:#f8fafc;border-radius:16px;'>
                    <h2 style='color:#1a56db;'>Admin Password Reset</h2>
                    <p style='color:#64748b;'>Your OTP for resetting the admin password:</p>
                    <div style='background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;text-align:center;margin:24px 0;'>
                        <span style='font-size:36px;font-weight:700;letter-spacing:8px;color:#0f172a;'>{$otp}</span>
                    </div>
                    <p style='color:#94a3b8;font-size:13px;'>Valid for 10 minutes. Do not share this OTP.</p>
                </div>";
            $mail->send();
            $message     = 'OTP sent to admin email.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message     = 'Failed to send OTP. Please try again.';
            $messageType = 'danger';
            $step = 1;
            unset($_SESSION['afp_step']);
        }
    }
}

// ── STEP 2: Verify OTP & reset ───────────────────────────────
if (isset($_POST['verify'])) {
    $entered_otp  = trim($_POST['otp']          ?? '');
    $new_password = $_POST['new_password']       ?? '';
    $confirm      = $_POST['confirm_password']   ?? '';

    if (!isset($_SESSION['afp_otp'], $_SESSION['afp_email'])) {
        $message = 'Session expired. Start over.';
        $messageType = 'danger'; $step = 1;
    } elseif ($entered_otp != $_SESSION['afp_otp']) {
        $message = 'Incorrect OTP.';
        $messageType = 'danger'; $step = 2;
    } elseif (strlen($new_password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $messageType = 'danger'; $step = 2;
    } elseif ($new_password !== $confirm) {
        $message = 'Passwords do not match.';
        $messageType = 'danger'; $step = 2;
    } else {
        $pdo    = getDB();
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt   = $pdo->prepare('UPDATE admin SET password = ? WHERE email = ?');
        $ok     = $stmt->execute([$hashed, $_SESSION['afp_email']]);

        if ($ok) {
            unset($_SESSION['afp_otp'], $_SESSION['afp_email'], $_SESSION['afp_step']);
            $message = 'Admin password updated successfully!';
            $messageType = 'success';
            $step = 3;
        } else {
            $message = 'Update failed. Try again.';
            $messageType = 'danger'; $step = 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Forgot Password — CampusCare</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="logo-wrap">
        <img src="dsce_logo.jpg" alt="DSCE Logo">
    </div>
    <h2>Admin Password Reset</h2>
    <p class="subtitle">
        <?= $step === 2 ? 'Enter the OTP sent to admin email' : ($step === 3 ? 'Password updated!' : 'Enter the admin email to receive an OTP') ?>
    </p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $messageType === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <form method="POST">
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" placeholder="admin email" required>
        </div>
        <button type="submit" name="send_otp" class="btn btn-primary">Send OTP</button>
    </form>

    <?php elseif ($step === 2): ?>
    <form method="POST">
        <div class="form-group">
            <label>OTP Code</label>
            <input type="text" name="otp" placeholder="6-digit OTP" required maxlength="6">
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Min. 8 characters" required minlength="8">
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter password" required>
        </div>
        <button type="submit" name="verify" class="btn btn-primary">Reset Password</button>
    </form>

    <?php else: ?>
    <div style="text-align:center;padding:20px 0;">
        <a href="admin_login.php" class="btn btn-primary">Go to Admin Login</a>
    </div>
    <?php endif; ?>

    <div class="auth-links">
        <a href="admin_login.php">← Back to Admin Login</a>
    </div>
</div>
</body>
</html>
