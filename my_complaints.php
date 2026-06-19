<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$pdo   = getDB();
$email = $_SESSION['email'];
$name  = $_SESSION['name'] ?? 'Student';

$stmt = $pdo->prepare("SELECT * FROM complaints WHERE email = ? ORDER BY created_at DESC");
$stmt->execute([$email]);
$complaints = $stmt->fetchAll();

$total    = count($complaints);
$pending  = count(array_filter($complaints, fn($r) => $r['status'] === 'Pending'));
$resolved = $total - $pending;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Complaints - CampusCare</title>
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
.topbar-actions { display:flex; align-items:center; gap:15px; }
.topbar-actions span { font-size:14px; }
.btn {
  text-decoration:none; padding:10px 16px;
  border-radius:8px; font-weight:600; transition:0.2s;
  border:none; cursor:pointer;
}
.btn-primary { background:#2563eb; color:white; }
.btn-primary:hover { background:#1d4ed8; }
.btn-danger { background:#dc2626; color:white; }
.btn-danger:hover { background:#b91c1c; }
.dashboard { max-width:1200px; margin:30px auto; padding:20px; }
.header {
  display:flex; justify-content:space-between;
  align-items:center; flex-wrap:wrap;
  gap:15px; margin-bottom:25px;
}
.page-title { font-size:32px; color:#1a3a6b; margin-bottom:5px; }
.page-subtitle { color:#666; }
.stats-row {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:20px; margin-bottom:25px;
}
.stat-card {
  background:white; border-radius:15px;
  padding:25px; box-shadow:0 4px 12px rgba(0,0,0,0.08);
  text-align:center;
}
.stat-value { font-size:36px; font-weight:bold; margin-bottom:8px; }
.stat-label { color:#666; font-size:14px; }
.card {
  background:white; border-radius:15px;
  padding:25px; box-shadow:0 4px 12px rgba(0,0,0,0.08);
}
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
th {
  background:#f1f5f9; color:#1a3a6b;
  text-align:left; padding:14px; font-size:14px;
}
td { padding:14px; border-bottom:1px solid #eee; font-size:14px; }
.badge { padding:6px 12px; border-radius:20px; font-size:12px; font-weight:bold; }
.badge-pending { background:#fef3c7; color:#92400e; }
.badge-resolved { background:#dcfce7; color:#166534; }
.empty-state { text-align:center; padding:60px 20px; }
.empty-state .icon { font-size:60px; margin-bottom:15px; }
.empty-state h3 { margin-bottom:10px; }
.empty-state p { color:#666; margin-bottom:20px; }
</style>
</head>
<body>

<nav class="topbar">
  <div class="topbar-brand">
    <img src="dsce_logo.jpg" alt="DSCE Logo">
    CampusCare
  </div>
  <div class="topbar-actions">
    <span>👋 <?= htmlspecialchars($name) ?></span>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="dashboard">
  <div class="header">
    <div>
      <h1 class="page-title">My Complaints</h1>
      <p class="page-subtitle">Track all your submitted complaints</p>
    </div>
    <a href="complaint.php" class="btn btn-primary">+ New Complaint</a>
  </div>

  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-value"><?= $total ?></div>
      <div class="stat-label">Total Complaints</div>
    </div>
    <div class="stat-card">
      <div class="stat-value" style="color:#f59e0b;"><?= $pending ?></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
      <div class="stat-value" style="color:#16a34a;"><?= $resolved ?></div>
      <div class="stat-label">Resolved</div>
    </div>
  </div>

  <div class="card">
    <?php if(empty($complaints)): ?>
      <div class="empty-state">
        <div class="icon">📋</div>
        <h3>No complaints submitted yet</h3>
        <p>Submit your first complaint now.</p>
        <a href="complaint.php" class="btn btn-primary">Submit Complaint</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Department</th>
              <th>Description</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($complaints as $row): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['department']) ?></td>
              <td style="max-width:250px;">
                <?= nl2br(htmlspecialchars($row['description'])) ?>
              </td>
              <td>
                <?php if($row['status'] == 'Resolved'): ?>
                  <span class="badge badge-resolved">✓ Resolved</span>
                <?php else: ?>
                  <span class="badge badge-pending">⏳ Pending</span>
                <?php endif; ?>
              </td>
              <td><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>