<?php
include 'db.php';

header('Content-Type: application/json');

$donation_id = $_GET['id'] ?? 0;

if ($donation_id > 0) {
    $stmt = $conn->prepare("SELECT volunteer_lat, volunteer_lng, status FROM donations WHERE id = ?");
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if ($data) {
        echo json_encode([
            'success' => true,
            'lat' => (float)$data['volunteer_lat'],
            'lng' => (float)$data['volunteer_lng'],
            'status' => $data['status']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Donation not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
}
?>
