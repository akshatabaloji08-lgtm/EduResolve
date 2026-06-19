<?php
session_start();
require_once 'config.php';

$entered_otp  = trim($_POST['otp']         ?? '');
$new_password = $_POST['newpassword']       ?? '';

if (!isset($_SESSION['otp'], $_SESSION['email'])) {
    header('Location: forgot.html?error=session_expired');
    exit();
}

if ($entered_otp != $_SESSION['otp']) {
    header('Location: verify_otp.html?error=invalid_otp');
    exit();
}

if (strlen($new_password) < 8) {
    header('Location: verify_otp.html?error=weak_password');
    exit();
}

$pdo    = getDB();
$hashed = password_hash($new_password, PASSWORD_BCRYPT);
$stmt   = $pdo->prepare('UPDATE students SET password = ? WHERE email = ?');
$ok     = $stmt->execute([$hashed, $_SESSION['email']]);

unset($_SESSION['otp'], $_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Reset — CampusCare</title>
<link rel="stylesheet" href="style.css">
<style>
body { display:flex; align-items:center; justify-content:center;
       min-height:100vh; background:var(--bg); padding:24px; }
.result-card { background:var(--surface); border-radius:var(--radius-xl);
               padding:48px 40px; max-width:400px; width:100%; text-align:center;
               box-shadow:var(--shadow-md); border:1px solid var(--border); animation:slideUp .4s ease; }
.icon { font-size:52px; margin-bottom:16px; }
h2 { font-family:var(--font-serif); font-size:22px; font-weight:400; margin-bottom:10px; }
p  { color:var(--text-muted); font-size:14px; margin-bottom:28px; }
</style>
</head>
<body>
<div class="result-card">
<?php if ($ok): ?>
    <div class="icon">✅</div>
    <h2>Password Updated</h2>
    <p>Your password has been reset successfully. You can now sign in.</p>
    <a href="login.php" class="btn btn-primary">Go to Login</a>
<?php else: ?>
    <div class="icon">❌</div>
    <h2>Reset Failed</h2>
    <p>Something went wrong. Please try again.</p>
    <a href="forgot_password.php" class="btn btn-primary">Try Again</a>
<?php endif; ?>
</div>
</body>
</html>
