<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

$pdo = getDB();
$html_page = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $usn        = trim($_POST['usn']        ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $department = trim($_POST['department'] ?? '');

    if (!$name || !$usn || !$email || !$password || !$department) {
        header('Location: register.html?error=missing');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: register.html?error=invalid_email');
        exit();
    }

    if (strlen($password) < 8) {
        header('Location: register.html?error=weak_password');
        exit();
    }

    $stmt = $pdo->prepare('SELECT id FROM students WHERE email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $html_page = 'exists';
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $ins = $pdo->prepare(
            'INSERT INTO students (name, usn, email, password, department) VALUES (?, ?, ?, ?, ?)'
        );
        $ok = $ins->execute([$name, $usn, $email, $hashed, $department]);
        $html_page = $ok ? 'success' : 'error';
    }
} else {
    header('Location: register.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration — CampusCare</title>
<link rel="stylesheet" href="style.css">
<style>
body { display:flex; align-items:center; justify-content:center; min-height:100vh;
       background:linear-gradient(135deg,#1a56db,#071d4a); padding:24px; }
.result-card { background:#fff; border-radius:var(--radius-xl); padding:48px 40px;
               max-width:400px; width:100%; text-align:center; box-shadow:var(--shadow-lg);
               animation:slideUp .4s ease; }
.icon { font-size:52px; margin-bottom:16px; }
h2 { font-family:var(--font-serif); font-size:22px; font-weight:400; margin-bottom:10px; }
p  { color:var(--text-muted); font-size:14px; margin-bottom:28px; }
</style>
</head>
<body>
<div class="result-card">
<?php if ($html_page === 'success'): ?>
    <div class="icon">✅</div>
    <h2>Registration Successful</h2>
    <p>Your account has been created. You can now log in.</p>
    <a href="login.php" class="btn btn-primary">Go to Login</a>
<?php elseif ($html_page === 'exists'): ?>
    <div class="icon">⚠️</div>
    <h2>Account Already Exists</h2>
    <p>An account with this email already exists. Please sign in.</p>
    <a href="login.php" class="btn btn-primary" style="margin-bottom:12px;">Go to Login</a>
    <br>
    <a href="forgot_password.php" class="btn btn-outline" style="margin-top:10px;">Forgot Password?</a>
<?php else: ?>
    <div class="icon">❌</div>
    <h2>Registration Failed</h2>
    <p>Something went wrong. Please try again.</p>
    <a href="register.html" class="btn btn-primary">Try Again</a>
<?php endif; ?>
</div>
</body>
</html>