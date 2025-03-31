<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['ids']) || !is_array($data['ids'])) {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit();
}

try {
    $ids = implode(',', array_map('intval', $data['ids']));
    $stmt = $pdo->prepare("DELETE FROM duty_logs WHERE id IN ($ids)");
    $stmt->execute();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error deleting duty logs: " . $e->getMessage()]);
}
?>