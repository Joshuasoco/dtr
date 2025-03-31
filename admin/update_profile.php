<?php
session_start();
require_once '../config/database.php';


// Ensure the user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin session details
$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    // Validate input fields
    if (empty($new_username) || empty($new_email)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit();
    }

    // Validate email format
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit();
    }

    // Update the database
    $stmt = $pdo->prepare("UPDATE admin SET name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$new_username, $new_email, $admin_id])) {
        // Update session data
        $_SESSION['user_name'] = $new_username;
        $_SESSION['user_email'] = $new_email;

        echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
}
?>
