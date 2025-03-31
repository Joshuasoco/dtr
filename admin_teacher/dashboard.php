<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher's department
$stmt = $pdo->prepare("SELECT department FROM teachers WHERE id = ?");
$stmt->execute([$teacher_id]);
$teacher_department = $stmt->fetch(PDO::FETCH_ASSOC)['department'];

// Fetch teacher's students count
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_students 
    FROM student_teacher_assignments sta
    JOIN students s ON sta.student_id = s.id
    WHERE sta.teacher_id = ? AND sta.status = 'Active'
");
$stmt->execute([$teacher_id]);
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];

// Fetch pending duty logs for teacher's assigned students
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS pending_logs 
    FROM duty_logs dl 
    JOIN students s ON dl.student_id = s.student_id
    JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE sta.teacher_id = ? AND sta.status = 'Active' AND dl.status = 'Pending'
");
$stmt->execute([$teacher_id]);
$pending_logs = $stmt->fetch(PDO::FETCH_ASSOC)['pending_logs'];

// Fetch approved duty logs
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS approved_logs 
    FROM duty_logs dl 
    JOIN students s ON dl.student_id = s.student_id
    JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE sta.teacher_id = ? AND sta.status = 'Active' AND dl.status = 'Approved'
");
$stmt->execute([$teacher_id]);
$approved_logs = $stmt->fetch(PDO::FETCH_ASSOC)['approved_logs'];

// Fetch rejected duty logs
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS rejected_logs 
    FROM duty_logs dl 
    JOIN students s ON dl.student_id = s.student_id
    JOIN student_teacher_assignments sta ON s.id = sta.student_id   
    WHERE sta.teacher_id = ? AND sta.status = 'Active' AND dl.status = 'Rejected'
");
$stmt->execute([$teacher_id]);
$rejected_logs = $stmt->fetch(PDO::FETCH_ASSOC)['rejected_logs'];

// Fetch year level statistics for teacher's assigned students
$stmt = $pdo->prepare("
    SELECT s.year_level, COUNT(*) as total 
    FROM student_teacher_assignments sta
    JOIN students s ON sta.student_id = s.id
    WHERE sta.teacher_id = ? AND sta.status = 'Active'
    GROUP BY s.year_level 
    ORDER BY s.year_level ASC
");
$stmt->execute([$teacher_id]);
$students_per_year = $stmt->fetchAll(PDO::FETCH_ASSOC);

$year_levels = [];
$year_counts = [];

foreach ($students_per_year as $row) {
    $year_levels[] = $row['year_level'];
    $year_counts[] = $row['total'];
}

// Convert to JSON for charts
$year_levels_json = json_encode($year_levels);
$year_counts_json = json_encode($year_counts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/teacher_sidebar.php'; ?>

        <main class="main-content">
            <header class="header-container">
                <div class="header-left">
                    <div class="sidebar-toggle" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                    <h2><i class="fa-solid fa-house"></i> Welcome to Teacher Dashboard</h2>
                </div>

                <div class="header-right">
                    <div class="date-picker-container">
                        <i class='far fa-calendar-alt'></i>
                        <input type="text" id="dateRange" class="date-input" readonly>
                        <i class='far fa-clock'></i>
                    </div>
                    <div class="notification-dropdown">
                        <a href="notification.php" class="notification-icon-link">
                            <button class="notification-icon" id="notificationToggle">
                                <i class="fa-solid fa-bell"></i>
                                <span class="badge" id="notificationCount" style="display: none;"></span>
                            </button>
                        </a>
                    </div>
                </div>
            </header>

            <section class="stats">
                <a href="teacher_students.php" class="stat-card blue">
                    <div class="icon-container">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Assigned Students</p>
                    </div>
                </a>
                <a href="approve_duty.php" class="stat-card yellow">
                    <div class="icon-container">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_logs; ?></h3>
                        <p>Pending Duty Logs</p>
                    </div>
                </a>
                <a href="approved_duties.php" class="stat-card green">
                    <div class="icon-container">
                        <i class="fas fa-check-square"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $approved_logs; ?></h3>
                        <p>Approved Duties</p>
                    </div>
                </a>
                <a href="rejected_duties.php" class="stat-card red">
                    <div class="icon-container">
                        <i class="fa-solid fa-thumbs-down"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $rejected_logs; ?></h3>
                        <p>Rejected Duties</p>
                    </div>
                </a>
            </section>

            <h3><i class="fa-solid fa-chart-simple"></i>&nbsp;&nbsp; Dashboard Analytics</h3>
     
            <div class="chart-table-container">
                <div class="chart-container">
                    <h3><i class="fa-solid fa-users"></i>&nbsp;Students Course</h3>
                    <canvas id="yearLevelBarChart"></canvas>
                </div>

                <div class="table-box">
                    <h3><i class="fa fa-chart-pie" aria-hidden="true"></i> Year Level Distribution</h3>
                    <div id="yearLevelChart"></div>
                </div>
            </div>

        </main>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById("yearLevelBarChart").getContext("2d");
        const yearLevels = <?php echo $year_levels_json; ?>;
        const yearCounts = <?php echo $year_counts_json; ?>;

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: yearLevels,
                datasets: [{
                    label: "Students Per Year Level",
                    data: yearCounts,
                    backgroundColor: [
                        'rgba(51, 116, 230, 0.2)',
                        'rgba(230, 57, 38, 0.2)',
                        'rgba(255, 165, 0, 0.2)',
                        'rgba(20, 164, 77, 0.2)',
                        'rgba(142, 36, 170, 0.2)'
                    ],
                    borderColor: [
                        'rgba(51, 116, 230, 1)',
                        'rgba(230, 57, 38, 1)',
                        'rgba(255, 165, 0, 1)',
                        'rgba(20, 164, 77, 1)',
                        'rgba(142, 36, 170, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
    google.charts.load("current", {packages: ["corechart"]});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var yearLevels = <?php echo $year_levels_json; ?>;
        var yearCounts = <?php echo $year_counts_json; ?>;

        var dataArray = [['Year Level', 'Total Students']];
        for (var i = 0; i < yearLevels.length; i++) {
            dataArray.push([yearLevels[i], yearCounts[i]]);
        }

        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            title: 'Students Per Year Level',
            titleTextStyle: {
                fontSize: 18,
                bold: true,
                color: '#333'
            },
            pieHole: 0.4,
            slices: {
                0: {color: '#3374E6'},
                1: {color: '#E63926'},
                2: {color: '#FFA500'},
                3: {color: '#14A44D'},
                4: {color: '#8E24AA'}
            }
        };

        var chart = new google.visualization.PieChart(document.getElementById('yearLevelChart'));
        chart.draw(data, options);
    }

function updateNotificationBadge() {
    fetch('fetch_notifications.php')
        .then(response => response.json())
        .then(data => {
            let notificationCount = document.getElementById('notificationCount');
            if (data.count > 0) {
                notificationCount.textContent = data.count;
                notificationCount.style.display = 'inline-block';
            } else {
                notificationCount.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

// Update notifications when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationBadge();
    // Refresh notifications every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});

    </script>
</body>
</html>