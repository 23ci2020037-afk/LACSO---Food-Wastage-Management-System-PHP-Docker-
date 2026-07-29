<?php
session_start();
include 'db.php';
$userId = $_SESSION['user_id'];
$result = $conn->query("SELECT COUNT(*) as total FROM donations WHERE user_id = $userId");
$row = $result->fetch_assoc();
echo json_encode(['total' => $row['total']]);
?>