<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

try {
    // Count the number of pending duty logs
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM duty_logs WHERE status = 'Pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['count' => $result['unread_count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>