<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['ids']) && is_array($data['ids'])) {
    $logIds = $data['ids'];

    // Prepare SQL to update the status of the selected logs
    $placeholders = implode(',', array_fill(0, count($logIds), '?'));
    $sql = "UPDATE duty_logs SET status = 'Pending' WHERE id IN ($placeholders)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($logIds);
        
        // Respond with success message
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // Respond with error message
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>