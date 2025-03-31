<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['ids']) && is_array($data['ids'])) {
    $logIds = $data['ids'];
    
    try {
        $pdo->beginTransaction();

        // First get all student IDs affected by these logs
        $placeholders = implode(',', array_fill(0, count($logIds), '?'));
        $stmt_students = $pdo->prepare("SELECT DISTINCT student_id FROM duty_logs WHERE id IN ($placeholders)");
        $stmt_students->execute($logIds);
        $affected_students = $stmt_students->fetchAll(PDO::FETCH_COLUMN);

        // Update the logs to pending
        $stmt_update = $pdo->prepare("
            UPDATE duty_logs 
            SET status = 'Pending',
                hours_worked = NULL,
                total_hours = NULL,
                approved_at = NULL,
                admin_id = NULL
            WHERE id IN ($placeholders)
        ");
        $stmt_update->execute($logIds);

        // For each affected student, recalculate their total hours
        foreach ($affected_students as $student_id) {
            // Calculate new total from remaining approved logs
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
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>