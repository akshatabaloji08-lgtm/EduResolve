<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']  ?? '');
    $password = $_POST['password']    ?? '';

    echo "Email: " . $email . "<br>";
    echo "Password: " . $password . "<br>";

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM admin WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if($admin){
        echo "Admin found! ✅<br>";
        echo "Hash: " . $admin['password'] . "<br>";
        $verify = password_verify($password, $admin['password']);
        echo "Verify: " . ($verify ? "TRUE ✅" : "FALSE ❌") . "<br>";

        if($verify){
            session_regenerate_id(true);
            $_SESSION['admin']      = $email;
            $_SESSION['admin_name'] = $admin['name'] ?? 'Admin';
            echo "Redirecting to admin.php...<br>";
            header('Location: admin.php');
            exit();
        }
    } else {
        echo "Admin NOT found! ❌<br>";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — CampusCare</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="logo-wrap">
        <img src="dsce_logo.jpg" alt="DSCE Logo">
    </div>
    <h2>Admin Portal</h2>
    <p class="subtitle">Sign in to manage complaints</p>

    <?php if ($error): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" placeholder="admin@campuscare.in"
                   required autocomplete="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter admin password"
                   required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary">Sign In</button>
    </form>

    <div class="auth-links">
        <a href="forgot_admin_password.php">Forgot password?</a>
        &nbsp;·&nbsp;
        <a href="index.html">← Back to Home</a>
    </div>
</div>
</body>
</html>