<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit();
}

$pdo        = getDB();
$adminName  = $_SESSION['admin_name'] ?? 'Admin';

// ── Filters ──────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$filter = $_GET['status'] ?? 'all';

$params = [];
$where  = [];

if ($search !== '') {
    $where[]  = '(student_name LIKE ? OR title LIKE ? OR department LIKE ? OR email LIKE ?)';
    $params   = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($filter === 'pending')  { $where[] = "status = 'Pending'"; }
if ($filter === 'resolved') { $where[] = "status = 'Resolved'"; }

$sql = 'SELECT * FROM complaints';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Stats
$totalStmt    = $pdo->query("SELECT COUNT(*) FROM complaints");
$total        = $totalStmt->fetchColumn();
$pendingStmt  = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='Pending'");
$pending      = $pendingStmt->fetchColumn();
$resolved     = $total - $pending;

// Flash message
$flash = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted')  $flash = 'Complaint deleted successfully.';
    if ($_GET['msg'] === 'resolved') $flash = 'Complaint marked as resolved.';
    if ($_GET['msg'] === 'error')    $flash = 'An error occurred. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — CampusCare</title>
<link rel="stylesheet" href="style.css">
<style>
.action-btns { display:flex; gap:6px; flex-wrap:wrap; }
.confirm-del { display:inline; }
</style>
</head>
<body>

<!-- Top bar -->
<nav class="topbar">
    <div class="topbar-brand">
        <img src="dsce_logo.jpg" alt="DSCE">
        CampusCare Admin
    </div>
    <div class="topbar-actions">
        <span style="font-size:14px;color:var(--text-muted);">👋 <?= htmlspecialchars($adminName) ?></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="dashboard fade-in">

    <div style="margin-bottom:24px;">
        <h1 class="page-title">Complaints Dashboard</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Manage and resolve all student complaints</p>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= str_contains($flash, 'error') ? 'danger' : 'success' ?>" style="margin-bottom:20px;">
        <?= str_contains($flash, 'error') ? '⚠️' : '✅' ?> <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Total Complaints</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--warning);"><?= $pending ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--success);"><?= $resolved ?></div>
            <div class="stat-label">Resolved</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--primary);">
                <?= $total > 0 ? round(($resolved / $total) * 100) : 0 ?>%
            </div>
            <div class="stat-label">Resolution Rate</div>
        </div>
    </div>

    <div class="card">
        <!-- Search & Filter -->
        <form method="GET" class="search-bar" style="margin-bottom:20px;">
            <input type="text" name="search" placeholder="Search by name, title, dept, email…"
                   value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="all"      <?= $filter === 'all'      ? 'selected' : '' ?>>All Status</option>
                <option value="pending"  <?= $filter === 'pending'  ? 'selected' : '' ?>>Pending</option>
                <option value="resolved" <?= $filter === 'resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm" style="width:auto;">Filter</button>
            <?php if ($search || $filter !== 'all'): ?>
            <a href="admin.php" class="btn btn-outline btn-sm" style="width:auto;">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($complaints)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h3>No complaints found</h3>
            <p style="color:var(--text-muted);">Try adjusting the search or filter.</p>
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Department</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $row): ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:13px;"><?= (int)$row['id'] ?></td>
                    <td>
                        <div style="font-weight:500;font-size:14px;"><?= htmlspecialchars($row['student_name']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($row['email']) ?></div>
                    </td>
                    <td style="font-size:13px;"><?= htmlspecialchars($row['department']) ?></td>
                    <td style="font-weight:500;font-size:14px;"><?= htmlspecialchars($row['title']) ?></td>
                    <td style="max-width:220px;font-size:13px;color:var(--text-muted);">
                        <?= htmlspecialchars(mb_strimwidth($row['description'], 0, 80, '…')) ?>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'Resolved'): ?>
                            <span class="badge badge-resolved">✓ Resolved</span>
                        <?php else: ?>
                            <span class="badge badge-pending">⏳ Pending</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;font-size:12px;color:var(--text-muted);">
                        <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                        <?= date('g:i a', strtotime($row['created_at'])) ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <?php if ($row['status'] !== 'Resolved'): ?>
                            <a href="update_status.php?id=<?= (int)$row['id'] ?>"
                               class="btn btn-success btn-sm"
                               onclick="return confirm('Mark this complaint as resolved?')">
                               ✓ Resolve
                            </a>
                            <?php endif; ?>

                            <a href="download_pdf.php?id=<?= (int)$row['id'] ?>"
                               class="btn btn-outline btn-sm">
                               ↓ PDF
                            </a>

                            <a href="delete.php?id=<?= (int)$row['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this complaint permanently? This cannot be undone.')">
                               Delete
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:14px;font-size:13px;color:var(--text-muted);">
            Showing <?= count($complaints) ?> of <?= $total ?> complaints
        </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
