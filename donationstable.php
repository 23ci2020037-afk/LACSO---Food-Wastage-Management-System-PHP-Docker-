<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donor_id = $_SESSION['user_id'];
    $food_name = $_POST['food_name'];
    $quantity  = $_POST['quantity'];
    $serves    = $_POST['serves'];
    $expiry    = $_POST['expiry'];
    $category  = $_POST['category'];
    $notes     = $_POST['notes'];
    $image_url = $_POST['image_url'];

    $sql = "INSERT INTO donations 
            (donor_id, food_name, quantity, serves, expiry, category, notes, image_url, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$donor_id, $food_name, $quantity, $serves, $expiry, $category, $notes, $image_url])) {
        echo "✅ Donation submitted successfully!";
    } else {
        echo "❌ Error: Could not submit donation.";
    }
}
?>
