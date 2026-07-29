<?php
include 'db.php';
session_start();

if (isset($_POST['donation_id'])) {
    $donation_id = $_POST['donation_id'];
    $volunteer_id = $_SESSION['user_id'];

    $sql = "UPDATE donations SET status='accepted', accepted_by=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $volunteer_id, $donation_id);
    if ($stmt->execute()) {
        echo "Task accepted successfully!";
    } else {
        echo "Failed to accept.";
    }
}
?>
