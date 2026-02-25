<?php
// PostgreSQL Connection
$env = file_exists(__DIR__ . '/.env') ? parse_ini_file(__DIR__ . '/.env') : [];

$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '5432';
$dbname = $env['DB_NAME'] ?? 'hospital_db';
$username = $env['DB_USER'] ?? 'postgres'; // Default PostgreSQL user
$password = $env['DB_PASS'] ?? ''; // Load from .env

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