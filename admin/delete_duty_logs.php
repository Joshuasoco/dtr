<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
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
    $pdo->beginTransaction();

    // First get all affected students and their log status
    $placeholders = implode(',', array_fill(0, count($data['ids']), '?'));
    $stmt_affected = $pdo->prepare("
        SELECT DISTINCT student_id 
        FROM duty_logs 
        WHERE id IN ($placeholders) 
        AND status = 'Approved'
    ");
    $stmt_affected->execute($data['ids']);
    $affected_students = $stmt_affected->fetchAll(PDO::FETCH_COLUMN);

    // Delete the logs
    $stmt_delete = $pdo->prepare("DELETE FROM duty_logs WHERE id IN ($placeholders)");
    $stmt_delete->execute($data['ids']);

    // Update total hours for each affected student
    foreach ($affected_students as $student_id) {
        // Recalculate total hours from remaining approved logs
        $stmt_total = $pdo->prepare("
            SELECT IFNULL(SUM(hours_worked), 0)
            FROM duty_logs
            WHERE student_id = ?
            AND status = 'Approved'
        ");
        $stmt_total->execute([$student_id]);
        $new_total = $stmt_total->fetchColumn();

        // Update student's total hours
        $stmt_student = $pdo->prepare("
            UPDATE students
            SET total_hours = ?
            WHERE student_id = ?
        ");
        $stmt_student->execute([$new_total, $student_id]);

        // Update all remaining approved logs for this student
        $stmt_logs = $pdo->prepare("
            UPDATE duty_logs
            SET total_hours = ?
            WHERE student_id = ?
            AND status = 'Approved'
        ");
        $stmt_logs->execute([$new_total, $student_id]);
    }

    $pdo->commit();
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "error" => "Error deleting duty logs: " . $e->getMessage()]);
}
?>