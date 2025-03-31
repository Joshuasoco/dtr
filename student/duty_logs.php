<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Check if the user is logged in (either student or admin)
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Fetch student duty logs from the database
$student_id = $_SESSION['student_id'];  // Student ID from session

$stmt = $pdo->prepare("SELECT * FROM duty_logs WHERE student_id = ? ORDER BY duty_date DESC, time_in DESC");
$stmt->execute([$student_id]);
$duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there are any duty logs
if (!$duty_logs) {
    $error = "No duty logs found.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duty Logs</title>
    <link rel="stylesheet" href="../assets/student.css">
</head>

<body>

    <div class="duty-logs-container">
        <header>
            <h2>Your Duty Logs</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </header>

        <!-- Display error or success messages -->
        <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
        <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Duty Logs Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Duty Date</th> <!-- Added Duty Date Column -->
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration (hours)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($duty_logs)): ?>
                <?php foreach ($duty_logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['id']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($log['duty_date'])); ?></td> <!-- Display Duty Date -->
                    <td><?php echo date('g:i A', strtotime($log['time_in'])); ?></td>
                    <td><?php echo ($log['time_out']) ? date('g:i A', strtotime($log['time_out'])) : 'N/A'; ?></td>

                    <td>
                        <?php 
                            if ($log['time_out']) {
                                $time_in = strtotime($log['time_in']);
                                $time_out = strtotime($log['time_out']);
                                $hours = round(($time_out - $time_in) / 3600, 2);
                                echo $hours. " hrs";
                            } else {
                                echo "N/A";
                            }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($log['status']); ?></td>
                    <td>
                        <?php if ($log['status'] === 'Pending'): ?>
                        <a href="edit_duty_log.php?id=<?php echo $log['id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7">No duty logs found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>