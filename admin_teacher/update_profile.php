<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form values
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);

    // Ensure department is selected
    if (empty($department)) {
        echo json_encode(["status" => "error", "message" => "Please select a department."]);
        exit();
    }

    // Update teacher profile in database
    $stmt = $pdo->prepare("UPDATE teachers SET name = ?, email = ?, department = ? WHERE id = ?");
    if ($stmt->execute([$username, $email, $department, $teacher_id])) {
        // ✅ Update the session to reflect the new department
        $_SESSION['teacher_dept'] = $department;

        echo json_encode(["status" => "success", "message" => "Profile updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update profile."]);
    }
}
?>
