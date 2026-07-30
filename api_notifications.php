<?php
include 'db.php';
header("Content-Type: application/json");

$last_user_id = isset($_GET['last_user_id']) ? (int)$_GET['last_user_id'] : 0;
$last_donation_id = isset($_GET['last_donation_id']) ? (int)$_GET['last_donation_id'] : 0;
$donor_user_id = isset($_GET['donor_user_id']) ? (int)$_GET['donor_user_id'] : 0;

$response = [
    'users' => [],
    'donations' => [],
    'accepted' => [],
    'auto_assigned' => [],
    'new_last_user_id' => $last_user_id,
    'new_last_donation_id' => $last_donation_id
];

// --- 1. Auto-Assignment Dispatcher (If pending > 3 minutes) ---
try {
    // Find donations pending for more than 3 minutes
    $auto_query = "SELECT id, food_name, donor_name FROM donations 
                   WHERE status = 'Pending' 
                   AND (created_at <= DATE_SUB(NOW(), INTERVAL 3 MINUTE) OR created_at <= DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 3 MINUTE))";
    $auto_res = $conn->query($auto_query);

    if ($auto_res && $auto_res->num_rows > 0) {
        // Fetch list of volunteers
        $v_list = [];
        $v_res = $conn->query("SELECT id, name FROM users WHERE role = 'volunteer'");
        if ($v_res && $v_res->num_rows > 0) {
            while ($v_row = $v_res->fetch_assoc()) {
                $v_list[] = $v_row;
            }
        }
        if (empty($v_list)) {
            $v_list = [
                ['id' => 1, 'name' => 'Volunteer 1'],
                ['id' => 2, 'name' => 'Volunteer 2'],
                ['id' => 3, 'name' => 'Volunteer 3']
            ];
        }

        $v_index = 0;
        while ($d_row = $auto_res->fetch_assoc()) {
            $assigned_v = $v_list[$v_index % count($v_list)];
            $v_index++;

            $v_id = (int)$assigned_v['id'];
            $v_name = $assigned_v['name'];
            $d_id = (int)$d_row['id'];

            $up_stmt = $conn->prepare("UPDATE donations SET status = 'Accepted', volunteer_id = ?, volunteer_name = ? WHERE id = ? AND status = 'Pending'");
            if ($up_stmt) {
                $up_stmt->bind_param("isi", $v_id, $v_name, $d_id);
                if ($up_stmt->execute() && $up_stmt->affected_rows > 0) {
                    $response['auto_assigned'][] = [
                        'id' => $d_id,
                        'food_name' => $d_row['food_name'],
                        'donor_name' => $d_row['donor_name'],
                        'volunteer_name' => $v_name
                    ];
                }
            }
        }
    }
} catch (Throwable $e) {
    // Suppress auto-assign errors
}

// First load init
if ($last_user_id == 0 && $last_donation_id == 0) {
    try {
        $res = $conn->query("SELECT MAX(id) as max_id FROM users");
        if ($res && $row = $res->fetch_assoc()) {
            $response['new_last_user_id'] = (int)($row['max_id'] ?? 0);
        }

        $res = $conn->query("SELECT MAX(id) as max_id FROM donations");
        if ($res && $row = $res->fetch_assoc()) {
            $response['new_last_donation_id'] = (int)($row['max_id'] ?? 0);
        }
    } catch (Throwable $e) {}

    echo json_encode($response);
    exit;
}

// Fetch new users
try {
    $user_query = $conn->prepare("SELECT id, name, role FROM users WHERE id > ?");
    if ($user_query) {
        $user_query->bind_param("i", $last_user_id);
        $user_query->execute();
        $user_res = $user_query->get_result();

        while ($row = $user_res->fetch_assoc()) {
            $response['users'][] = $row;
            if ($row['id'] > $response['new_last_user_id']) {
                $response['new_last_user_id'] = (int)$row['id'];
            }
        }
    }
} catch (Throwable $e) {}

// Fetch new donations
try {
    $donation_query = $conn->prepare("SELECT id, donor_name, food_name, quantity, status, created_at FROM donations WHERE id > ?");
    if ($donation_query) {
        $donation_query->bind_param("i", $last_donation_id);
        $donation_query->execute();
        $don_res = $donation_query->get_result();

        while ($row = $don_res->fetch_assoc()) {
            $response['donations'][] = $row;
            if ($row['id'] > $response['new_last_donation_id']) {
                $response['new_last_donation_id'] = (int)$row['id'];
            }
        }
    }
} catch (Throwable $e) {}

// Fetch recently accepted/updated donations
try {
    if ($donor_user_id > 0) {
        $acc_stmt = $conn->prepare("SELECT id, food_name, status, volunteer_name 
                                    FROM donations 
                                    WHERE (user_id = ? OR donor_id = ?) AND status IN ('Accepted', 'Collected', 'Delivered') 
                                    ORDER BY id DESC LIMIT 5");
        if ($acc_stmt) {
            $acc_stmt->bind_param("ii", $donor_user_id, $donor_user_id);
            $acc_stmt->execute();
            $acc_res = $acc_stmt->get_result();
            while ($row = $acc_res->fetch_assoc()) {
                $response['accepted'][] = $row;
            }
        }
    } else {
        $acc_res = $conn->query("SELECT id, donor_name, food_name, status, volunteer_name 
                                 FROM donations 
                                 WHERE status IN ('Accepted', 'Collected', 'Delivered') 
                                 ORDER BY id DESC LIMIT 5");
        if ($acc_res) {
            while ($row = $acc_res->fetch_assoc()) {
                $response['accepted'][] = $row;
            }
        }
    }
} catch (Throwable $e) {}

echo json_encode($response);
?>
