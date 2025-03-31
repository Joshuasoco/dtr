<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['teacher_id'])) {
    $teacher_id = $_GET['teacher_id'];
    
    try {
        // Get teacher info
        $teacher_stmt = $pdo->prepare("SELECT name, department FROM teachers WHERE id = ?");
        $teacher_stmt->execute([$teacher_id]);
        $teacher = $teacher_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$teacher) {
            throw new Exception('Teacher not found');
        }
        
        // Get students in the same department
        $student_stmt = $pdo->prepare("
            SELECT id, name, email, year_level, section 
            FROM students 
            WHERE department = ? 
            ORDER BY year_level, section, name
        ");
        $student_stmt->execute([$teacher['department']]);
        $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'teacher' => $teacher,
            'students' => $students
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
