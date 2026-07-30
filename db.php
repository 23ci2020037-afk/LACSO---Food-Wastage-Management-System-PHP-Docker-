<?php
// Centralized Persistent Session Management
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 86400 * 30);
    ini_set('session.gc_maxlifetime', 86400 * 30);
    if (!headers_sent()) {
        @session_set_cookie_params([
            'lifetime' => 86400 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}

// Database connection settings (Dual mode: Docker + Local XAMPP support)
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$db   = getenv('DB_NAME') ?: 'lacso_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Attempt primary connection (Environment DB or localhost)
    $conn = new mysqli($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    try {
        // Fallback for local XAMPP setups with different database names
        $conn = new mysqli('localhost', 'root', '', 'lacso_dataa');
    } catch (mysqli_sql_exception $ex) {
        die("Connection failed: " . $ex->getMessage());
    }
}

$conn->set_charset("utf8mb4");

// Centralized Safe Database Auto-Migration / Schema Healer
try {
    // 1. Ensure `users` table exists
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('donor', 'volunteer', 'admin', 'ngo') NOT NULL DEFAULT 'donor',
        phone VARCHAR(20),
        city VARCHAR(50),
        points INT DEFAULT 0,
        co2_saved DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Ensure all required columns exist in `users`
    $userCols = [
        'phone'       => "VARCHAR(20) DEFAULT NULL",
        'city'        => "VARCHAR(50) DEFAULT NULL",
        'points'      => "INT DEFAULT 0",
        'co2_saved'   => "DECIMAL(10,2) DEFAULT 0.00",
        'created_at'  => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    foreach ($userCols as $col => $type) {
        try {
            $conn->query("ALTER TABLE users ADD COLUMN $col $type");
        } catch (Throwable $e) {}
    }

    // 2. Ensure `donations` table exists
    $conn->query("CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT 0,
        donor_id INT DEFAULT 0,
        donor_name VARCHAR(100) DEFAULT 'Donor',
        donor_phone VARCHAR(20) DEFAULT NULL,
        food_name VARCHAR(100) NOT NULL,
        quantity VARCHAR(50) NOT NULL,
        serves VARCHAR(50) DEFAULT NULL,
        expiry VARCHAR(50) DEFAULT NULL,
        category VARCHAR(50) DEFAULT NULL,
        pickup_address TEXT DEFAULT NULL,
        drop_address TEXT DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        status ENUM('Pending', 'Accepted', 'Collected', 'Delivered') DEFAULT 'Pending',
        volunteer_id INT DEFAULT NULL,
        volunteer_name VARCHAR(100) DEFAULT NULL,
        pickup_time VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Ensure all required columns exist in `donations`
    $donationCols = [
        'user_id'        => "INT DEFAULT 0",
        'donor_id'       => "INT DEFAULT 0",
        'donor_name'     => "VARCHAR(100) DEFAULT 'Donor'",
        'donor_phone'    => "VARCHAR(20) DEFAULT NULL",
        'serves'         => "VARCHAR(50) DEFAULT NULL",
        'expiry'         => "VARCHAR(50) DEFAULT NULL",
        'category'       => "VARCHAR(50) DEFAULT NULL",
        'pickup_address' => "TEXT DEFAULT NULL",
        'drop_address'   => "TEXT DEFAULT NULL",
        'notes'          => "TEXT DEFAULT NULL",
        'image_path'     => "VARCHAR(255) DEFAULT NULL",
        'status'         => "ENUM('Pending', 'Accepted', 'Collected', 'Delivered') DEFAULT 'Pending'",
        'volunteer_id'   => "INT DEFAULT NULL",
        'volunteer_name' => "VARCHAR(100) DEFAULT NULL",
        'pickup_time'    => "VARCHAR(100) DEFAULT NULL",
        'created_at'     => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    foreach ($donationCols as $col => $type) {
        try {
            $conn->query("ALTER TABLE donations ADD COLUMN $col $type");
        } catch (Throwable $e) {}
    }

    // 3. Ensure default test accounts exist
    $defaultUsers = [
        ['Volunteer 1', 'volunteer1', 'vol123', 'volunteer'],
        ['Volunteer 2', 'volunteer2', 'vol123', 'volunteer'],
        ['Volunteer 3', 'volunteer3', 'vol123', 'volunteer'],
        ['Admin', 'admin', 'admin', 'admin'],
        ['Care Foundation NGO', 'ngo', 'ngo123', 'ngo'],
        ['Hope Orphanage NGO', 'ngo1', 'ngo123', 'ngo']
    ];
    foreach ($defaultUsers as $u) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
        if ($chk) {
            $chk->bind_param("s", $u[1]);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                $ins = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $ins->bind_param("ssss", $u[0], $u[1], $u[2], $u[3]);
                $ins->execute();
            }
        }
    }
} catch (Throwable $t) {
    // Suppress schema auto-healing errors
}
?>
