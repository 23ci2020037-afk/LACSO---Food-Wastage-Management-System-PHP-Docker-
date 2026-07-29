<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data->name, $data->email, $data->password, $data->role);
        if ($stmt->execute()) sendResponse("success", "Registered");
        else sendResponse("error", "Failed");
        break;

    case 'login':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $data->email, $data->password);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) sendResponse("success", "OK", $user);
        else sendResponse("error", "Wrong credentials");
        break;

    case 'submit_donation':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO donations (user_id, donor_name, food_name, quantity, serves, expiry, category, pickup_address, drop_address, notes, pickup_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssss", $data->donor_id, $data->donor_name, $data->food_name, $data->quantity, $data->serves, $data->expiry, $data->category, $data->pickup_address, $data->drop_address, $data->notes, $data->time);
        if ($stmt->execute()) sendResponse("success", "Saved");
        else sendResponse("error", "Failed: " . $conn->error);
        break;

    case 'get_donations':
        $res = $conn->query("SELECT * FROM donations ORDER BY created_at DESC");
        sendResponse("success", "OK", $res->fetch_all(MYSQLI_ASSOC));
        break;

    default: sendResponse("error", "Invalid"); break;
}
?>
