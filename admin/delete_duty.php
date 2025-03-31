<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duty_id'])) {
    $duty_id = $_POST['duty_id'];
    
    try {
        $pdo->beginTransaction();
        
        // First get the student_id and check if the log was approved
        $stmt_get_log = $pdo->prepare("SELECT student_id, status FROM duty_logs WHERE id = ?");
        $stmt_get_log->execute([$duty_id]);
        $log_data = $stmt_get_log->fetch(PDO::FETCH_ASSOC);
        
        if ($log_data) {
            $student_id = $log_data['student_id'];
            $was_approved = ($log_data['status'] === 'Approved');
            
            // Delete the duty log
            $stmt = $pdo->prepare("DELETE FROM duty_logs WHERE id = ?");
            $stmt->execute([$duty_id]);
            
            // Only recalculate if the deleted log was approved
            if ($was_approved) {
                // Recalculate total hours from remaining approved logs
                $stmt_total_hours = $pdo->prepare("
                    SELECT IFNULL(SUM(hours_worked), 0) 
                    FROM duty_logs 
                    WHERE student_id = ? AND status = 'Approved'
                ");
                $stmt_total_hours->execute([$student_id]);
                $total_hours_rendered = $stmt_total_hours->fetchColumn();
                
                // Update student's total hours
                $stmt_student_update = $pdo->prepare("
                    UPDATE students 
                    SET total_hours = ? 
                    WHERE student_id = ?
                ");
                $stmt_student_update->execute([$total_hours_rendered, $student_id]);
                
                // Update all remaining approved logs for this student
                $stmt_logs_update = $pdo->prepare("
                    UPDATE duty_logs 
                    SET total_hours = ? 
                    WHERE student_id = ?
                    AND status = 'Approved'
                ");
                $stmt_logs_update->execute([$total_hours_rendered, $student_id]);
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Duty log not found');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>