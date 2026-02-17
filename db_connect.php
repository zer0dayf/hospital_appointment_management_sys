<?php
// PostgreSQL Connection
$host = 'localhost';
$port = '5432';
$dbname = 'hospital_db';
$username = 'postgres'; // Default PostgreSQL user
$password = '123456'; // Replace with your actual password

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'PostgreSQL connection failed: ' . $e->getMessage()]);
    exit;
}
?>