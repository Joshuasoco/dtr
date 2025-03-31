<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Count pending duty logs for teacher's assigned students
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS unread_count 
        FROM duty_logs dl 
        JOIN students s ON dl.student_id = s.student_id
        JOIN student_teacher_assignments sta ON s.id = sta.student_id
        WHERE sta.teacher_id = ? 
        AND sta.status = 'Active' 
        AND dl.status = 'Pending'
    ");
    $stmt->execute([$teacher_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['count' => $result['unread_count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>