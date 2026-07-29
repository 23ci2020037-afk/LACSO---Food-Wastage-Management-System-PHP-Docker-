<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: login.php");
    exit;
}

$volunteer_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$stmt = $conn->prepare("
    SELECT d.*, u.name as donor_name, u.phone as donor_phone
    FROM donations d
    JOIN users u ON d.user_id = u.id
    WHERE d.assigned_to = ?
    ORDER BY d.created_at DESC
");
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Volunteer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <h2>Welcome Volunteer, <?php echo htmlspecialchars($userName); ?></h2>
    <h3>Your Assigned Pickups</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>Food Name</th>
            <th>Donor Name</th>
            <th>Donor Phone</th>
            <th>Status</th>
            <th>Pickup Photo</th>
            <th>Upload Pickup</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['food_name']) ?></td>
                <td><?= htmlspecialchars($row['donor_name']) ?></td>
                <td><?= htmlspecialchars($row['donor_phone']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                  <?php if (!empty($row['picked_image'])): ?>
                    <img src="<?= htmlspecialchars($row['picked_image']) ?>" width="80">
                  <?php else: ?>
                    Not Uploaded
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($row['status']=='assigned' || $row['status']=='picked'): ?>
                    <form method="post" action="upload_pickup_image.php" enctype="multipart/form-data">
                      <input type="hidden" name="donation_id" value="<?= $row['id'] ?>">
                      <input type="file" name="pickup_image" required>
                      <button type="submit">Upload</button>
                    </form>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <form method="post" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>