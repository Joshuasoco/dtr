<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $target_dir = "../uploads/";
    
    // Ensure the upload directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $unique_name = uniqid() . "_" . basename($_FILES["profile_photo"]["name"]);
    $target_file = $target_dir . $unique_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is valid
    $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['status' => 'error', 'message' => 'File is not an image.']);
        exit();
    }

    // Check file size (limit: 500KB)
    if ($_FILES["profile_photo"]["size"] > 500000) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, your file is too large.']);
        exit();
    }

    // Allow only specific file formats
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.']);
        exit();
    }

    // Move file and update database
    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
        $stmt = $pdo->prepare("UPDATE admin SET profile_photo = ? WHERE id = ?");
        if ($stmt->execute([$unique_name, $admin_id])) {
            echo json_encode(['status' => 'success', 'filePath' => $target_file]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile photo in database.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error uploading file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or invalid request.']);
}
?>
