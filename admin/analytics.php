<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch total number of students and duty logs
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_students FROM students");
$stmt->execute();
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_logs FROM duty_logs");
$stmt->execute();
$total_logs = $stmt->fetch(PDO::FETCH_ASSOC)['total_logs'];

$stmt = $pdo->prepare("SELECT SUM(duration) AS total_hours FROM duty_logs WHERE status = 'Approved'");
$stmt->execute();
$total_hours = $stmt->fetch(PDO::FETCH_ASSOC)['total_hours'];

$stmt = $pdo->prepare("SELECT student_id, COUNT(*) AS total_logs, SUM(duration) AS total_hours FROM duty_logs WHERE status = 'Approved' GROUP BY student_id");
$stmt->execute();
$log_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <div class="analytics-container">
        <h2>Admin Analytics</h2>

        <div class="summary-stats">
            <p>Total Students: <?php echo $total_students; ?></p>
            <p>Total Duty Logs: <?php echo $total_logs; ?></p>
            <p>Total Approved Hours: <?php echo $total_hours; ?> hours</p>
        </div>

        <h3>Duty Logs by Student</h3>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Total Logs</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($log_details as $log): ?>
                <tr>
                    <td><?php echo $log['student_id']; ?></td>
                    <td><?php echo $log['total_logs']; ?></td>
                    <td><?php echo $log['total_hours']; ?> hours</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>