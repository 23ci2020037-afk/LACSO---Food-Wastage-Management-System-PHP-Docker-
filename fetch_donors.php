<?php
include 'db.php';

$donors = $conn->query("SELECT * FROM donors ORDER BY id DESC");

if ($donors && $donors->num_rows > 0) {
    while ($d = $donors->fetch_assoc()) {
        echo "<tr class='hover:bg-gray-50'>
                <td class='p-3'>" . htmlspecialchars($d['name']) . "</td>
                <td class='p-3'>" . htmlspecialchars($d['donation_type']) . "</td>
                <td class='p-3'>" . ucfirst(htmlspecialchars($d['status'])) . "</td>
                <td class='p-3'>" . htmlspecialchars($d['delivery_date']) . "</td>
                <td class='p-3 text-center'>
                    <button onclick=\"window.location.href='edit_donor.php?id=" . $d['id'] . "'\" class='bg-blue-100 p-1 rounded hover:bg-blue-200'>Edit</button>
                    <button onclick=\"if(confirm('Delete this donor?')) window.location.href='delete_donor.php?id=" . $d['id'] . "'\" class='bg-red-100 p-1 rounded hover:bg-red-200'>Delete</button>
                </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center text-gray-500 p-3'>No donors found.</td></tr>";
}
?>
