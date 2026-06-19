<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: admin.php?error=invalid_id');
    exit();
}

$pdo  = getDB();
$stmt = $pdo->prepare('DELETE FROM complaints WHERE id = ?');
$ok   = $stmt->execute([$id]);

header('Location: admin.php?msg=' . ($ok ? 'deleted' : 'error'));
exit();
