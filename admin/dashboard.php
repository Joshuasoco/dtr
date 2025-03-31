<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch stats
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_students FROM students");
$stmt->execute();
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS pending_logs FROM duty_logs WHERE status = 'Pending'");
$stmt->execute();
$pending_logs = $stmt->fetch(PDO::FETCH_ASSOC)['pending_logs'];

// Fetch approved duty logs
$stmt = $pdo->prepare("SELECT COUNT(*) AS approved_logs FROM duty_logs WHERE status = 'Approved'");
$stmt->execute();
$approved_logs = $stmt->fetch(PDO::FETCH_ASSOC)['approved_logs'];

// Fetch rejected duty logs
$stmt = $pdo->prepare("SELECT COUNT(*) AS rejected_logs FROM duty_logs WHERE status = 'Rejected'");
$stmt->execute();
$rejected_logs = $stmt->fetch(PDO::FETCH_ASSOC)['rejected_logs'];

$stmt = $pdo->prepare("SELECT department, COUNT(*) AS total FROM students GROUP BY department");
$stmt->execute();
$students_per_department = $stmt->fetchAll(PDO::FETCH_ASSOC);

$departments = [];
$student_counts = [];

foreach ($students_per_department as $row) {
    $departments[] = $row['department'];
    $student_counts[] = $row['total'];
}

// Convert data to JSON for JavaScript
$departments_json = json_encode($departments);
$student_counts_json = json_encode($student_counts);


try {
    $stmt = $pdo->prepare("SELECT year_level, COUNT(*) as total FROM students GROUP BY year_level ORDER BY year_level ASC");
    $stmt->execute();
    $students_per_year = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $year_levels = [];
    $year_counts = [];

    foreach ($students_per_year as $row) {
        $year_levels[] = $row['year_level'];
        $year_counts[] = $row['total'];
    }

    // Convert data to JSON for Chart.js
    $year_levels_json = json_encode($year_levels);
    $year_counts_json = json_encode($year_counts);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Add Chart.js library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="header-container">
                <div class="header-left">
                    <div class="sidebar-toggle" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                    <h2><i class="fa-solid fa-house"></i> Welcome to Admin Dashboard</h2>
                </div>



                <div class="header-right">
                    <div class="date-picker-container">
                        <i class='far fa-calendar-alt'></i>
                        <input type="text" id="dateRange" class="date-input" readonly>
                        <i class='far fa-clock'></i>
                    </div>
                    <div class="notification-dropdown">
                        <a href="notifications.php" class="notification-icon-link">
                            <button class="notification-icon" id="notificationToggle">
                                <i class="fa-solid fa-bell"></i>
                                <span class="badge" id="notificationCount" style="display: none;"></span>
                            </button>
                        </a>
                    </div>


                </div>
            </header>
            <section class="stats">
                <a href="total_students.php" class="stat-card blue">
                    <div class="icon-container">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </a>
                <a href="approve_duty.php" class="stat-card yellow">
                    <!--pending_duties.php-->
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
            <!--chart-->
            <h3><i class="fa-solid fa-chart-simple"></i>&nbsp;&nbsp; Dashboard Analytics</h3>
            <div class="chart-table-container">
                <div class="chart-container">
                    <h3><i class="fa-solid fa-users"></i> Total Students in Different Departments
                    </h3>
                    <canvas id="scholarChart"></canvas>
                </div>

                <div class="table-box">
                    <h3><i class="fa fa-bar-chart" aria-hidden="true"></i> Students Per Year Level</h3>
                    <div id="yearLevelChart">
                    </div>
                </div>
        </main>
    </div>

    <div class="settings-button" id="settingsButton">
        <i class="fas fa-cog"></i>
    </div>

    <div class="settings-sidebar" id="settingsSidebar">
        <div class="settings-header">
            <h3>Settings Panel</h3>
            <button id="closeSettings"><i class="fas fa-times"></i></button>
        </div>

        <div class="settings-content">
            <div class="settings-section">
                <label for="inputFormat">Input Text Format</label>
                <select id="inputFormat">
                    <option value="default">Default</option>
                    <option value="uppercase">Uppercase</option>
                    <option value="lowercase">Lowercase</option>
                </select>
            </div>

            <!-- Dark Mode Toggle Button -->
            <div class="settings-section">
                <label class="theme-selection-label">Select Theme</label>
                <div class="theme-selection">
                    <div id="lightModeBox" class="theme-box light-mode active" data-theme="light-mode">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div id="darkModeBox" class="theme-box dark-mode" data-theme="dark-mode">
                        <i class="fas fa-moon"></i>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <label class="color-theme-label">Select Sidebar Theme:</label>
                <div class="color-theme-selection">
                    <div class="color-circle active" data-color="#343a40" style="background-color: #343a40;"></div>
                    <div class="color-circle" data-color="#1B5379" style="background-color: #1B5379;"></div>
                    <div class="color-circle" data-color="#127328" style="background-color: #127328"></div>
                    <div class="color-circle" data-color="#008B8B" style="background-color: #008B8B;"></div>
                    <div class="color-circle" data-color="#3E1254" style="background-color: #3E1254"></div>
                </div>
            </div>

            <div class="settings-section">
                <label>Select a Background:</label>
                <div id="bgImageContainer">
                    <img src="../assets/image/background_image.jpg" class="bg-option" data-image="background1.jpg">
                    <img src="../assets/image/background_image.jpg" class="bg-option" data-image="background2.jpg">
                    <img src="../assets/image/background_image.jpg" class="bg-option" data-image="background3.jpg">
                    <img src="../assets/image/background_image.jpg" class="bg-option" data-image="background4.jpg">
                    <img src="../assets/image/background_image.jpg" class="bg-option" data-image="background5.jpg">
                    <img src="../assets/image/background_image.jpg" class="bg-option" data-image="background6.jpg">
                </div>
                <button id="removeBgImage">Remove Background</button>
            </div>
        </div>
    </div>

    <script src="../assets/dashboard.js"></script>

    <script>
    const departments = <?php echo $departments_json; ?>;
    const studentCounts = <?php echo $student_counts_json; ?>;




    google.charts.load("current", {
        packages: ["corechart"]
    });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        // Ensure PHP variables are properly echoed in JSON format
        var yearLevels = <?php echo json_encode($year_levels); ?>;
        var yearCounts = <?php echo json_encode($year_counts); ?>;

        // Prepare data for Google Charts
        var dataArray = [
            ['Year Level', 'Total Students']
        ];
        for (var i = 0; i < yearLevels.length; i++) {
            dataArray.push([yearLevels[i], yearCounts[i]]);
        }

        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            title: 'Students Per Year Level',
            titleTextStyle: {
                fontSize: 18, // Adjust the font size
                bold: true,
                color: '#333', // Dark gray for better visibility

            },
            pieHole: 0.4, // Donut effect
            slices: {
                0: {
                    color: '#3374E6'
                }, // Blue
                1: {
                    color: '#E63926'
                }, // Red
                2: {
                    color: '#FFA500'
                }, // Orange
                3: {
                    color: '#14A44D'
                }, // Green
                4: {
                    color: '#8E24AA'
                } // Purple
            }
        };

        var chart = new google.visualization.PieChart(document.getElementById('yearLevelChart'));
        chart.draw(data, options);
    }
    </script>
</body>

</html>