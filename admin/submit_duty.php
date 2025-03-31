<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $duty_date = $_POST['duty_date'];
    $time_in = $_POST['time_in'];

    try {
        $pdo->beginTransaction(); // Start transaction

        // Retrieve student details
        $student_stmt = $pdo->prepare("SELECT name, year_level FROM students WHERE student_id = ?");
        $student_stmt->execute([$student_id]);
        $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit();
        }

        $student_name = $student['name'];
        $year_level = $student['year_level'];

        // Insert duty log
        $stmt = $pdo->prepare("
            INSERT INTO duty_logs (student_id, duty_date, time_in, status)
            VALUES (?, ?, ?, 'Pending')
        ");
        if (!$stmt->execute([$student_id, $duty_date, $time_in])) {
            throw new Exception("Duty log insertion failed: " . implode(" | ", $stmt->errorInfo()));
        }

        // Get last inserted duty log ID
        $duty_id = $pdo->lastInsertId();

        // Insert notification
        $time_out = NULL;
        $notification_stmt = $pdo->prepare("
            INSERT INTO notifications (student_id, name, year_level, duty_date, time_in, time_out, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$notification_stmt->execute([$student_id, $student_name, $year_level, $duty_date, $time_in, $time_out, 'unread'])) {
            throw new Exception("Notification insertion failed: " . implode(" | ", $notification_stmt->errorInfo()));
        }

        $pdo->commit(); // Commit transaction

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack(); // Rollback if anything fails
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>