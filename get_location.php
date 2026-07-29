<?php
include 'db.php';
$donation_id = $_GET['donation_id'] ?? 0;

$stmt = $conn->prepare("SELECT volunteer_name AS name, volunteer_lat AS lat, volunteer_lng AS lng FROM donations WHERE id=?");
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
  "name" => $result['name'] ?? 'Volunteer',
  "lat" => $result['lat'] ?? null,
  "lng" => $result['lng'] ?? null
]);
?>
