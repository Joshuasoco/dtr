<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get notifications (duty log statuses)
$stmt = $pdo->prepare("SELECT * FROM duty_logs WHERE student_id = ? ORDER BY duty_date DESC, time_in DESC");
$stmt->execute([$student_id]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/student.css">
</head>

<body>
    <div class="notifications-container">
        <header>
            <h2>Duty Log Status Notifications</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </header>

        <section class="notifications-list">
            <?php if ($notifications): ?>
            <ul>
                <?php foreach ($notifications as $notification): ?>
                <li data-status="<?php echo $notification['status']; ?>">
                    <strong>Duty Date:</strong> <?php echo date('Y-m-d', strtotime($notification['duty_date'])); ?><br>
                    <strong>Time In:</strong> <?php echo date('h:i A', strtotime($notification['time_in'])); ?><br>
                    <strong>Time Out:</strong>
                    <?php echo ($notification['time_out']) ? date('h:i A', strtotime($notification['time_out'])) : 'N/A'; ?><br>
                    <strong>Status:</strong> <span><?php echo htmlspecialchars($notification['status']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p>No duty log notifications.</p>
            <?php endif; ?>
        </section>

    </div>
</body>

</html>