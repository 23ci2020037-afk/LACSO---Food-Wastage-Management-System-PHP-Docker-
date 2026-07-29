<?php
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
?>
