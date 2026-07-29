<?php
$servername = "localhost";
$username = "root";
$password = ""; // apne hisaab se change karein
$dbname = "lacso_dataa";

// Form submit hua hai?
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $food_type = $_POST['food_type'];
    $quantity = $_POST['quantity'];
    $remarks = $_POST['remarks'];

    // DB connect
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Data insert karein
    $stmt = $conn->prepare("INSERT INTO donations (name, contact, address, food_type, quantity, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $contact, $address, $food_type, $quantity, $remarks);

    if ($stmt->execute()) {
        echo "Donation submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>