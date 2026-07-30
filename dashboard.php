<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?> 🎉</h2>
<p>You are logged in as: <?php echo htmlspecialchars($_SESSION['role'] ?? 'guest'); ?></p>
<a href="logout.php">Logout</a>
