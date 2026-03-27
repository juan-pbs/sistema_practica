<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$host = (string)(getenv('DB_HOST') ?: '127.0.0.1');
$port = (string)(getenv('DB_PORT') ?: '3306');
$dbname = (string)(getenv('DB_NAME') ?: 'sistemapractica');
$username = (string)(getenv('DB_USER') ?: 'root');
$password = (string)(getenv('DB_PASS') ?: '');

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    exit('No fue posible conectar con el sistema en este momento.');
}
