<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];
    
    try {
        // First, get the teacher's profile picture if exists
        $stmt = $pdo->prepare("SELECT profile_photo FROM teachers WHERE id = ?");
        $stmt->execute([$teacher_id]);
        $teacher = $stmt->fetch();
        
        // Delete the teacher
        $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
        $result = $stmt->execute([$teacher_id]);
        
        if ($result) {
            // If successful and there was a profile picture, delete it
            if ($teacher && $teacher['profile_photo']) {
                $profile_pic_path = "../uploads/teachers/" . $teacher['profile_photo'];
                if (file_exists($profile_pic_path)) {
                    unlink($profile_pic_path);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully']);
        } else {
            throw new Exception('Failed to delete teacher');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting teacher: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}