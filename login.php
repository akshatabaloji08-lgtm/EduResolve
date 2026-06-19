<?php
session_start();
if(isset($_SESSION['email'])) {
    header("Location: my_complaints.php");
    exit();
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config.php';
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result  = $stmt->get_result();
    $student = $result->fetch_assoc();

    if($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id']   = $student['id'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['student_usn']  = $student['usn'];
        $_SESSION['email']        = $student['email'];
        $_SESSION['name']         = $student['name'];
        header("Location: my_complaints.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login - CampusCare</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #1a3a6b 0%, #2563b0 50%, #1e4fa0 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .card {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 20px;
    padding: 40px 35px;
    width: 340px;
    text-align: center;
    color: white;
  }
  .logo-circle {
    width: 90px; height: 90px;
    border-radius: 50%;
    background: white;
    margin: 0 auto 20px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }
  .logo-circle img { width: 80px; height: 80px; object-fit: contain; }
  .college-name {
    font-size: 11px; letter-spacing: 1.5px;
    color: rgba(255,255,255,0.75);
    margin-bottom: 8px; text-transform: uppercase;
  }
  h1 { font-size: 26px; font-weight: 700; margin-bottom: 6px; }
  .tagline { font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 25px; }
  .form-group { margin-bottom: 15px; text-align: left; }
  label { font-size: 13px; color: rgba(255,255,255,0.8); display: block; margin-bottom: 6px; }
  input {
    width: 100%; padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.1);
    color: white; font-size: 14px;
    outline: none;
  }
  input::placeholder { color: rgba(255,255,255,0.45); }
  input:focus { border-color: rgba(255,255,255,0.7); }
  .btn {
    display: block; width: 100%; padding: 14px;
    border-radius: 10px; font-size: 15px; font-weight: 600;
    text-decoration: none; margin-bottom: 12px;
    transition: all 0.2s ease; cursor: pointer; border: none;
  }
  .btn-login { background: white; color: #1a3a6b; }
  .btn-login:hover { background: rgba(255,255,255,0.9); transform: translateY(-1px); }
  .error {
    background: rgba(255,80,80,0.2);
    border: 1px solid rgba(255,80,80,0.5);
    border-radius: 8px; padding: 10px;
    font-size: 13px; color: #ffaaaa; margin-bottom: 15px;
  }
  .links { margin-top: 5px; font-size: 13px; color: rgba(255,255,255,0.55); }
  .links a { color: rgba(255,255,255,0.75); text-decoration: none; font-weight: 600; }
  .links a:hover { color: white; text-decoration: underline; }
</style>
</head>
<body>
<div class="card">
  <div class="logo-circle">
    <img src="dsce_logo.jpg" alt="DSCE Logo">
  </div>
  <p class="college-name">Dayananda Sagar College of Engineering</p>
  <h1>Student Login</h1>
  <p class="tagline">CampusCare Portal</p>

  <?php if($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" placeholder="Enter your college email"
             required autocomplete="new-password">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter your password"
             required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-login">Login</button>
  </form>

  <p class="links">
    <a href="forgot_password.php">Forgot Password?</a> &nbsp;|&nbsp;
    <a href="index.html">Back to Home</a>
  </p>
</div>
</body>
</html>