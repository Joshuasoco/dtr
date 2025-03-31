<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['ids']) || empty($data['ids'])) {
    echo json_encode(["success" => false, "error" => "No duty logs selected"]);
    exit();
}

$admin_id = $_SESSION['admin_id'];
$log_ids = $data['ids'];

try {
    $pdo->beginTransaction();

    foreach ($log_ids as $log_id) {
        // Fetch duty log details
        $stmt_log = $pdo->prepare("SELECT time_in, time_out, student_id, duty_date FROM duty_logs WHERE id = ?");
        $stmt_log->execute([$log_id]);
        $log_data = $stmt_log->fetch(PDO::FETCH_ASSOC);

        if ($log_data) {
            $time_in = $log_data['time_in'];
            $time_out = $log_data['time_out'];
            $duty_date = $log_data['duty_date'];

            // Calculate hours worked
            $hours_worked = ($time_in && $time_out) ? 
                round((strtotime($time_out) - strtotime($time_in)) / 3600, 2) : 0;

            // Approve the duty log
            $stmt_update = $pdo->prepare("
                UPDATE duty_logs 
                SET status = 'Approved', 
                    hours_worked = ?, 
                    admin_id = ?, 
                    approved_at = NOW() 
                WHERE id = ?
            ");
            $stmt_update->execute([$hours_worked, $admin_id, $log_id]);

            // Get total hours including current log
            $stmt_total_hours = $pdo->prepare("
                SELECT IFNULL(SUM(hours_worked), 0) 
                FROM duty_logs 
                WHERE student_id = ? AND status = 'Approved'
            ");
            $stmt_total_hours->execute([$log_data['student_id']]);
            $total_hours_rendered = $stmt_total_hours->fetchColumn();

            // Update student's total hours
            $stmt_student_update = $pdo->prepare("
                UPDATE students 
                SET total_hours = ? 
                WHERE student_id = ?
            ");
            $stmt_student_update->execute([$total_hours_rendered, $log_data['student_id']]);

            // Update all approved logs for this student with the new total
            $stmt_logs_update = $pdo->prepare("
                UPDATE duty_logs 
                SET total_hours = ? 
                WHERE student_id = ?
                AND status = 'Approved'
            ");
            $stmt_logs_update->execute([$total_hours_rendered, $log_data['student_id']]);
        }
    }

    $pdo->commit();
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>