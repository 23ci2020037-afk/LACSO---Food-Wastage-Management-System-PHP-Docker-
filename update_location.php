<?php
include 'db.php';

$donation_id = $_POST['donation_id'] ?? 0;
$lat = $_POST['lat'] ?? 0;
$lng = $_POST['lng'] ?? 0;

if ($donation_id > 0) {
    $stmt = $conn->prepare("UPDATE donations SET volunteer_lat=?, volunteer_lng=? WHERE id=?");
    $stmt->bind_param("ddi", $lat, $lng, $donation_id);
    $stmt->execute();
    echo "ok";
} else {
    echo "error";
}
?>
