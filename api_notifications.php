<?php
include 'db.php';
header("Content-Type: application/json");

$last_user_id = isset($_GET['last_user_id']) ? (int)$_GET['last_user_id'] : 0;
$last_donation_id = isset($_GET['last_donation_id']) ? (int)$_GET['last_donation_id'] : 0;

$response = [
    'users' => [],
    'donations' => [],
    'new_last_user_id' => $last_user_id,
    'new_last_donation_id' => $last_donation_id
];

// If it's 0 (first load), just get the current max IDs so we don't spam 100 notifications on page load
if ($last_user_id == 0) {
    $res = $conn->query("SELECT MAX(id) as max_id FROM users");
    $row = $res->fetch_assoc();
    $response['new_last_user_id'] = $row['max_id'] ? $row['max_id'] : 0;
    
    $res = $conn->query("SELECT MAX(id) as max_id FROM donations");
    $row = $res->fetch_assoc();
    $response['new_last_donation_id'] = $row['max_id'] ? $row['max_id'] : 0;
    
    echo json_encode($response);
    exit;
}

// Fetch new users
$user_query = $conn->prepare("SELECT id, name, role FROM users WHERE id > ?");
$user_query->bind_param("i", $last_user_id);
$user_query->execute();
$user_res = $user_query->get_result();

while ($row = $user_res->fetch_assoc()) {
    $response['users'][] = $row;
    if ($row['id'] > $response['new_last_user_id']) {
        $response['new_last_user_id'] = $row['id'];
    }
}

// Fetch new donations
$donation_query = $conn->prepare("SELECT id, donor_name, food_name FROM donations WHERE id > ?");
$donation_query->bind_param("i", $last_donation_id);
$donation_query->execute();
$don_res = $donation_query->get_result();

while ($row = $don_res->fetch_assoc()) {
    $response['donations'][] = $row;
    if ($row['id'] > $response['new_last_donation_id']) {
        $response['new_last_donation_id'] = $row['id'];
    }
}

echo json_encode($response);
?>
