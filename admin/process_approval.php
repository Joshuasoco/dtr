<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['log_id'], $_POST['action'])) {
    $log_id = $_POST['log_id'];
    $action = $_POST['action'];

    // Update duty log status
    $stmt = $pdo->prepare("UPDATE duty_logs SET status = ? WHERE id = ?");
    if ($stmt->execute([$action, $log_id])) {
        header("Location: approve_duty.php");
        exit();
    } else {
        echo "Error processing approval.";
    }
}
?>