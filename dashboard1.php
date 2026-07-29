<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require 'db.php';
$id = $_POST['id'];
$action = $_POST['action']; // 'discard' or 'approve'
if ($action == 'discard') {
    $stmt = $pdo->prepare("UPDATE users SET status='discarded' WHERE id=? AND role='volunteer'");
    $stmt->execute([$id]);
} else if ($action == 'approve') {
    $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='volunteer'");
    $stmt->execute([$id]);
}
echo "success";
?>