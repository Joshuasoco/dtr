<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Ensure the user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get all pending duty log submissions (not yet approved/rejected)
$stmt = $pdo->prepare("
    SELECT dl.id, dl.duty_date, dl.time_in, dl.time_out, dl.status, 
           s.student_id, s.name, s.course, s.department, s.year_level
    FROM duty_logs dl
    JOIN students s ON dl.student_id = s.student_id
    WHERE dl.status = 'Pending'
    ORDER BY dl.duty_date DESC, dl.time_in DESC
");
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Notifications</title>
    <link rel="stylesheet" href="../assets/notifications.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <script src="../assets/dashboard.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="../assets/search_filter.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'?>

        <main class="main-content">
            <div class="page-title">Notifications</div>
            
            <header class="header-container">
                <div class="pending-page">
                    <div class="header-left">
                        <h2><i class="fa-solid fa-hourglass-half"></i> Notification</h2>
                    </div>
                </div>
            </header>
            
            <section class="notifications-container">
                <div class="notifications-header">
                    <h2>NOTIFICATIONS</h2>
                    <div class="actions">
                        <button class="mark-all-read">Mark All as Read</button>
                        <button class="clear-all">Clear All</button>
                    </div>
                </div>
                
                <div class="notifications-list">
                    <ul>
                    <?php if (!empty($notifications)) : ?>
                        <?php foreach ($notifications as $duty) : ?>
                        <li class="notification-item pending">
                            <div class="notification-icon">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-header">
                                    <div class="notification-title">
                                        Pending Duty: <?php echo htmlspecialchars($duty['name']); ?>
                                    </div>
                                    <div class="notification-time">
                                        <i class="fa-regular fa-clock"></i> 
                                        <?php echo htmlspecialchars($duty['duty_date']); ?>
                                    </div>
                                    <div class="notification-actions">
                                        <button class="mark-read"><i class="fas fa-check"></i> Mark as Read</button>
                                    </div>
                                </div>
                                <div class="notification-message">
                                    Student ID: <?php echo htmlspecialchars($duty['student_id']); ?><br>
                                    Time In: <?php echo date("h:i A", strtotime($duty['time_in'])); ?><br>
                                    Time Out: <?php echo $duty['time_out'] ? date("h:i A", strtotime($duty['time_out'])) : 'N/A'; ?><br>
                                    Hours Worked: 
                                    <?php 
                                        if ($duty['time_out']) {
                                            $timeIn = strtotime($duty['time_in']);
                                            $timeOut = strtotime($duty['time_out']);
                                            $hoursWorked = ($timeOut - $timeIn) / 3600; // Convert seconds to hours
                                            echo number_format($hoursWorked, 2) . ' hrs';
                                        } else {
                                            echo 'N/A';
                                        }
                                    ?>
                                </div>
                                <div class="notification-footer">
                                    <div class="notification-user"><?php echo htmlspecialchars($duty['name']); ?></div>
                                    <span class="statuspending">Pending</span>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li class="notification-item">
                            <div class="notification-content">
                                <div class="notification-message">No Notification found.</div>
                            </div>
                        </li>
                    <?php endif; ?>
                    </ul>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
