<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = trim($_SESSION['student_id']); // Ensure student_id is trimmed

// Debug: Log student_id to check its value
error_log("Debug: Profile page session student_id = " . $student_id);

// Get student information
$stmt = $pdo->prepare("SELECT * FROM students WHERE BINARY student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Error: Student ID does not exist in the system. Debug ID: " . htmlspecialchars($student_id));
}

// Get duty logs
$stmt = $pdo->prepare("SELECT * FROM duty_logs WHERE student_id = ? ORDER BY duty_date DESC, time_in DESC");
$stmt->execute([$student_id]);
$duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../assets/student.css">
    <style>
    .profile-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .duty-logs table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .duty-logs th,
    .duty-logs td {
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
    }

    .duty-logs th {
        background: #007bff;
        color: white;
    }

    .duty-logs tr:nth-child(even) {
        background: #f2f2f2;
    }
    </style>
</head>

<body>
    <div class="profile-container">
        <header>
            <h2>Student Profile</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </header>

        <section class="personal-info">
            <h3>Personal Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name'] ?? 'N/A'); ?></p>
            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></p>
            <p><strong>Scholarship Type:</strong> <?php echo htmlspecialchars($student['scholarship_type'] ?? 'N/A'); ?>
            </p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></p>
            <p><strong>Year Level:</strong> <?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></p>
            <p><strong>HK Duty Status:</strong> <?php echo htmlspecialchars($student['hk_duty_status'] ?? 'N/A'); ?></p>
        </section>

        <section class="duty-logs">
            <h3>Your Duty Logs</h3>
            <?php if (!empty($duty_logs)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Duty Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Duration (Hours)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($duty_logs as $log): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($log['duty_date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
                        <td>
                            <?php echo ($log['time_out']) ? date('h:i A', strtotime($log['time_out'])) : 'N/A'; ?>
                        </td>
                        <td><?php echo number_format($log['duration'], 2); ?></td>
                        <td><?php echo htmlspecialchars($log['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No duty logs available.</p>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>