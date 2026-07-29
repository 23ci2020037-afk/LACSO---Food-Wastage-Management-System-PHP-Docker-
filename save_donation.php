<?php
session_start();
include 'db.php'; // Using centralized connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User session not found. Please log in.");
}

$user_id = $_SESSION['user_id']; 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $donor_name     = $_POST['donor_name'] ?? '';
        $donor_phone    = $_POST['donor_phone'] ?? '';
        $food_name      = $_POST['food_name'];
        $quantity       = $_POST['quantity'];
        $serves         = $_POST['serves'] ?? '';
        $expiry         = $_POST['expiry'] ?? '';
        $category       = $_POST['category'] ?? '';
        $pickup_address = $_POST['pickup_address'] ?? '';
        $drop_address   = $_POST['drop_address'] ?? '';
        $notes          = $_POST['notes'] ?? '';
    
        // File upload
        $image_path = "";
        if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] == UPLOAD_ERR_OK) {
            $imgName = uniqid('food_', true) . '.' . pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION);
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $image_path = $uploadDir . $imgName;
            move_uploaded_file($_FILES['food_image']['tmp_name'], $image_path);
        }
    
        $hasDonorId = $conn->query("SHOW COLUMNS FROM donations LIKE 'donor_id'")->num_rows > 0;

        if ($hasDonorId) {
            $sql = "INSERT INTO donations (user_id, donor_id, donor_name, donor_phone, food_name, quantity, serves, expiry, category, pickup_address, drop_address, image_path, notes, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iisssssssssss', $user_id, $user_id, $donor_name, $donor_phone, $food_name, $quantity, $serves, $expiry, $category, $pickup_address, $drop_address, $image_path, $notes);
        } else {
            $sql = "INSERT INTO donations (user_id, donor_name, food_name, quantity, serves, expiry, category, pickup_address, drop_address, image_path, notes, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('issssssssss', $user_id, $donor_name, $food_name, $quantity, $serves, $expiry, $category, $pickup_address, $drop_address, $image_path, $notes);
        }

        if ($stmt->execute()) {
            
            // --- Impact & Gamification Logic ---
            $servesNum = (int)$serves;
            // E.g., each serve saves ~0.5kg of food, avoiding ~1.2kg of CO2. 
            // If serves isn't provided, use a flat 2.5kg base impact.
            $co2Impact = ($servesNum > 0) ? ($servesNum * 1.2) : 2.5; 
            $pointsEarned = 10;
            
            // Only update if columns exist (graceful fallback if user hasn't run the alter yet)
            $conn->query("UPDATE users SET points = points + $pointsEarned, co2_saved = co2_saved + $co2Impact WHERE id = $user_id");

            header("Location: donor.php?success=1");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    
        $stmt->close();
    $conn->close();
}
?>
