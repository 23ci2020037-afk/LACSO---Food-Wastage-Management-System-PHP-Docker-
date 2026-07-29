<?php
session_start();
include 'db.php'; // database connection

if (!isset($_SESSION['user_name'])) {
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donation_id'])) {
    $donationId = intval($_POST['donation_id']);

    // Delete the donation
    $stmt = $conn->prepare("DELETE FROM donations WHERE id=?");
    $stmt->bind_param("i", $donationId);
    $stmt->execute();

    header("Location: volunteer_dashboard.php");
    exit;
} else {
    echo "Invalid request.";
}
?>
