<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<h2>Welcome, <?php echo $_SESSION['name']; ?> 🎉</h2>
<p>You are logged in as: <?php echo $_SESSION['role']; ?></p>
<a href="logout.php">Logout</a>
<?php
session_start();
session_destroy();
header("Location: login.php");
exit;
?>
