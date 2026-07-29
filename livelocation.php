<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'msg'=>'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$lat = $_POST['latitude'] ?? '';
$lng = $_POST['longitude'] ?? '';

if ($lat && $lng) {
    $stmt = $conn->prepare("SELECT id FROM live_location WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE live_location SET latitude=?, longitude=?, updated_at=NOW() WHERE user_id=?");
        $stmt->bind_param("ddi", $lat, $lng, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO live_location (user_id, latitude, longitude, updated_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("idd", $user_id, $lat, $lng);
    }
    if($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'msg'=>'DB error']);
    }
} else {
    echo json_encode(['success'=>false, 'msg'=>'Missing coordinates']);
}
?>