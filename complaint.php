<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$session_name  = $_SESSION['name']  ?? '';
$session_email = $_SESSION['email'] ?? '';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = trim($_POST['student_name'] ?? '');
    $department   = trim($_POST['department']   ?? '');
    $title        = trim($_POST['title']        ?? '');
    $description  = trim($_POST['description']  ?? '');
    $email        = $session_email;

    if (!$student_name || !$department || !$title || !$description) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO complaints (student_name, department, title, description, email, status, created_at)
             VALUES (?, ?, ?, ?, ?, \'Pending\', NOW())'
        );
        $ok = $stmt->execute([$student_name, $department, $title, $description, $email]);
        if ($ok) {
            $success = true;
        } else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Complaint - CampusCare</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; background:#f4f7fb; min-height:100vh; }
.topbar {
  background:#1a3a6b; color:white;
  padding:15px 25px;
  display:flex; justify-content:space-between; align-items:center;
}
.topbar-brand {
  display:flex; align-items:center; gap:10px;
  font-size:22px; font-weight:bold;
}
.topbar-brand img {
  width:42px; height:42px;
  border-radius:50%; background:white; padding:3px;
}
.topbar-actions { display:flex; align-items:center; gap:15px; font-size:14px; }
.page-wrap {
  display:flex; align-items:center; justify-content:center;
  min-height:calc(100vh - 72px); padding:30px 20px;
}
.card {
  background:white; border-radius:16px;
  padding:40px 35px; width:100%; max-width:460px;
  box-shadow:0 4px 20px rgba(0,0,0,0.10);
}
.card h2 { font-size:24px; color:#1a3a6b; margin-bottom:6px; }
.card .subtitle { font-size:13px; color:#888; margin-bottom:28px; }
.form-group { margin-bottom:18px; }
label {
  display:block; font-size:11px; font-weight:700;
  letter-spacing:1px; text-transform:uppercase;
  color:#555; margin-bottom:7px;
}
input, select, textarea {
  width:100%; padding:12px 14px;
  border:1px solid #dde3ee; border-radius:10px;
  font-size:14px; color:#333; outline:none;
  transition:border-color 0.2s; font-family:Arial,sans-serif;
}
input:focus, select:focus, textarea:focus { border-color:#2563eb; }
input[readonly] { background:#f1f5f9; color:#888; cursor:not-allowed; }
textarea { resize:vertical; min-height:120px; }
select { background:white; }
.btn {
  display:inline-block; padding:13px 20px;
  border-radius:10px; font-size:15px; font-weight:600;
  text-decoration:none; border:none; cursor:pointer; transition:all 0.2s;
}
.btn-primary { background:#2563eb; color:white; width:100%; text-align:center; }
.btn-primary:hover { background:#1d4ed8; }
.btn-danger { background:#dc2626; color:white; }
.btn-danger:hover { background:#b91c1c; }
.alert { padding:12px 15px; border-radius:10px; font-size:13px; margin-bottom:18px; }
.alert-error { background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; }
.success-state { text-align:center; padding:20px 0; }
.success-state .icon { font-size:56px; margin-bottom:16px; }
.success-state h2 { color:#16a34a; margin-bottom:10px; }
.success-state p { color:#666; font-size:14px; margin-bottom:25px; }
.back-link {
  display:block; text-align:center; margin-top:15px;
  font-size:13px; color:#2563eb; text-decoration:none;
}
.back-link:hover { text-decoration:underline; }
</style>
</head>
<body>

<nav class="topbar">
  <div class="topbar-brand">
    <img src="dsce_logo.jpg" alt="DSCE Logo">
    CampusCare
  </div>
  <div class="topbar-actions">
    <span>👋 <?= htmlspecialchars($session_name) ?></span>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="page-wrap">
  <div class="card">

    <?php if ($success): ?>
      <div class="success-state">
        <div class="icon">✅</div>
        <h2>Complaint Submitted!</h2>
        <p>Your complaint has been received.<br>The admin will review it and update the status.</p>
        <a href="my_complaints.php" class="btn btn-primary">View My Complaints</a>
        <a href="complaint.php" class="back-link">← Submit Another Complaint</a>
      </div>
    <?php else: ?>
      <h2>Submit a Complaint</h2>
      <p class="subtitle">Fill in the details below. We'll respond as soon as possible.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Your Full Name</label>
          <input type="text" name="student_name"
                 value="<?= htmlspecialchars($session_name) ?>"
                 placeholder="e.g. Priya Sharma" required>
        </div>

        <div class="form-group">
          <label>Department</label>
          <select name="department" required>
            <option value="" disabled selected>Select your department</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Information Science">Information Science</option>
            <option value="Electronics & Communication">Electronics & Communication</option>
            <option value="Electrical Engineering">Electrical Engineering</option>
            <option value="Mechanical Engineering">Mechanical Engineering</option>
            <option value="Civil Engineering">Civil Engineering</option>
            <option value="Artificial Intelligence & ML">Artificial Intelligence & ML</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email"
                 value="<?= htmlspecialchars($session_email) ?>"
                 readonly>
        </div>

        <div class="form-group">
          <label>Complaint Title</label>
          <input type="text" name="title"
                 placeholder="Brief title of your complaint" required>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="description"
                    placeholder="Describe your complaint in detail..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Complaint</button>
      </form>

      <a href="my_complaints.php" class="back-link">← Back to My Complaints</a>
    <?php endif; ?>

  </div>
</div>

</body>
</html>