<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];  // Primary ID (hidden field in the form)
    $student_id = trim($_POST['student_id']); // Editable Student ID
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $department = trim($_POST['department']);
    $scholarship_type = trim($_POST['scholarship_type']);
    $hk_duty_status = trim($_POST['hk_duty_status']);
    $year_level = trim($_POST['year_level']);

    // Check if required fields are empty
    if (empty($student_id) || empty($name) || empty($email) || empty($course) || empty($department) || empty($hk_duty_status)) {
        echo "<p style='color:red;'>All fields are required.</p>";
    } else {
        // ✅ Corrected SQL Query with 9 placeholders
        $stmt = $pdo->prepare("UPDATE students SET 
            student_id = ?, 
            name = ?, 
            email = ?, 
            course = ?, 
            department = ?, 
            scholarship_type = ?, 
            hk_duty_status = ?, 
            year_level = ? 
            WHERE id = ?");

        // ✅ Corrected execution with 9 values
        if ($stmt->execute([$student_id, $name, $email, $course, $department, $scholarship_type, $hk_duty_status, $year_level, $id])) {
            echo "<p style='color:green;'>Profile updated successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error updating profile.</p>";
        }
    }
}
?>