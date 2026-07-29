<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

// Fetch all donations with donor name
$stmt = $pdo->prepare("SELECT d.*, u.name FROM donation d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
$stmt->execute();
$donations = $stmt->fetchAll();
?>

<table border="1" cellpadding="10">
    <tr>
        <th>Donor Name</th>
        <th>Food Item</th>
        <th>Quantity</th>
        <th>Date/Time</th>
    </tr>
    <?php foreach($donations as $donation): ?>
    <tr>
        <td><?php echo htmlspecialchars($donation['name']); ?></td>
        <td><?php echo htmlspecialchars($donation['food_item']); ?></td>
        <td><?php echo htmlspecialchars($donation['quantity']); ?></td>
        <td><?php echo $donation['created_at']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
