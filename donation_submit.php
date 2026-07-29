<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $food_name = $_POST['food_name'];
    $quantity = $_POST['quantity'];
    $serves = $_POST['serves'] ?? '';
    $expiry = $_POST['expiry'] ?? '';
    $category = $_POST['category'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Handle file upload
    $imagePath = '';
    if(isset($_FILES['food_image']) && $_FILES['food_image']['error'] === 0){
        $ext = pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION);
        $imagePath = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['food_image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO donations (user_id, food_name, quantity, serves, expiry, category, notes, food_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $food_name, $quantity, $serves, $expiry, $category, $notes, $imagePath);

    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}
