<?php
// database.php - Database connection setup

$host = 'localhost'; // Your database host
$dbname = 'duty_tracker'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password (use environment variables or encryption for security in production)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>