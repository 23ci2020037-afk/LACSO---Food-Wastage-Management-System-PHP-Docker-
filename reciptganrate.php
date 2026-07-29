<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donation_id = $_POST['donation_id'];
    $donor_id = $_POST['donor_id'];
    $volunteer_id = $_POST['volunteer_id'];
    $receipt_number = uniqid("RCP-");
    $notes = $_POST['notes'];

    $sql = "INSERT INTO receipts (donation_id, donor_id, volunteer_id, receipt_number, notes) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$donation_id, $donor_id, $volunteer_id, $receipt_number, $notes])) {
        $conn->prepare("UPDATE donations SET status = 'completed' WHERE id = ?")
            ->bind_param("i", $donation_id)
            ->execute();
        echo "✅ Receipt generated successfully!";
    } else {
        echo "❌ Error generating receipt.";
    }
}
?>