<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donation_id = $_POST['donation_id'];
    $volunteer_id = $_POST['volunteer_id'];

    $sql = "INSERT INTO tasks (donation_id, volunteer_id, status) VALUES (?, ?, 'open')";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$donation_id, $volunteer_id])) {
        $conn->prepare("UPDATE donations SET status='assigned', assigned_to=? WHERE id=?")
            ->bind_param("ii", $volunteer_id, $donation_id)
            ->execute();
        echo "✅ Task assigned to volunteer.";
    } else {
        echo "❌ Error assigning task.";
    }
}
?>