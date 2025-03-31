<?php
session_start();
require_once '../config/database.php';


if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];


if (!isset($_GET['student_id'])) {
    echo "Student ID not provided.";
    exit();
}

$student_id = $_GET['student_id'];

$stmt = $pdo->prepare("
    SELECT * FROM duty_logs 
    WHERE student_id = ? 
    ORDER BY duty_date DESC, time_in DESC
");
$stmt->execute([$student_id]);
$duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_hours = 0;
foreach ($duty_logs as $log) {
    if ($log['status'] === 'Approved' && $log['time_out']) { // Only count Approved logs
        $time_in = strtotime($log['time_in']);
        $time_out = strtotime($log['time_out']);
        $hours = round(($time_out - $time_in) / 3600, 2);
        $total_hours += $hours;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Logs</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <script src="../assets/search_filter.js"></script>
    <style>
    .dropdown {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        min-width: 140px;
        z-index: 1;
        right: 0;
        border-radius: 5px;
        padding: 5px 0;
    }

    .dropdown-content a {
        color: black;
        padding: 8px 12px;
        text-decoration: none;
        display: block;
        cursor: pointer;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/teacher_sidebar.php'; ?>
        <main class="main-content">
            <header class="header-container">
                <div class="header-left">
                    <h2>
                        <i class="fa-solid fa-arrow-left" onclick="window.location.href='teacher_students.php'"
                            style="cursor: pointer;"></i>
                        <i class="fa-regular fa-clock"></i>
                        Student View Logs
                    </h2>
                </div>
                <div class="header-right">

                    <div class="header-right">
                        <div class="search-sort-container">
                            <div class="search-container">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchInput" placeholder="Search...">
                            </div>

                            <div class="dropdown">
                                <img src="../assets/image/sort-icon.jpg" alt="Sort" onclick="toggleDropdown()">
                                <div class="dropdown-content" id="dropdown">
                                    <select id="sortSelect">
                                        <option value="" disabled selected>--Filter--</option>
                                        <option value="id">ID</option>
                                        <option value="student_id">Student ID</option>
                                        <option value="name">Name</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <section class="table-container">
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Duty Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Worked</th>
                            <th>Status
                                <div class="dropdown">
                                    <img src="../assets/image/drop-status.png" alt="Filter Status"
                                        style="width: 18px; height: 18px; cursor: pointer; margin-right: 5px;">
                                    <div class="dropdown-content">
                                        <a onclick="filterByStatus('All')">Show All</a>
                                        <a onclick="filterByStatus('Approved')">Approved</a>
                                        <a onclick="filterByStatus('Pending')">Pending</a>
                                        <a onclick="filterByStatus('Rejected')">Rejected</a>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($duty_logs)): ?>
                        <?php foreach ($duty_logs as $log): ?>
                        <tr class="log-row" data-status="<?php echo $log['status']; ?>">
                            <td><?php echo date('Y-m-d', strtotime($log['duty_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
                            <td><?php echo ($log['time_out']) ? date('h:i A', strtotime($log['time_out'])) : 'N/A'; ?>
                            </td>
                            <td>
                                <?php 
        // Check if the status is 'Rejected'
        if ($log['status'] === 'Rejected') {
            // If the log is rejected, show '0 hours'
            echo "0 hrs";
        } else {
            // If the log is not rejected and time_out is available
            if ($log['time_out']) {
                // Convert time_in and time_out to timestamps
                $time_in = strtotime($log['time_in']);
                $time_out = strtotime($log['time_out']);
                $total_seconds = $time_out - $time_in;

                // Calculate hours and minutes
                $hours = floor($total_seconds / 3600); // Get full hours
                $minutes = round(($total_seconds % 3600) / 60); // Get remaining minutes

                // Display the result with hours and minutes
                if ($hours > 0) {
                    if ($minutes > 0) {
                        echo "{$hours} hr" . ($hours > 1 ? "s" : "") . " {$minutes} min";
                    } else {
                        echo "{$hours} hr" . ($hours > 1 ? "s" : "");
                    }
                } else {
                    // If there are no hours, only show minutes (excluding "0 min" when no minutes)
                    echo $minutes > 0 ? "{$minutes} min" : "0 min";
                }
            } else {
                // If no time_out, show '0 hrs'
                echo "0 hrs";
            }
        }
    ?>
                            </td>
                            <td class="
    <?php 
        echo ($log['status'] == 'Pending') ? 'status-pending' : 
             (($log['status'] == 'Approved') ? 'status-approved' : 
             (($log['status'] == 'Rejected') ? 'status-rejected' : '')); 
    ?>
">
                                <?php 
        if ($log['status'] == 'Pending') {
            echo '<i class="fa-solid fa-clock"></i> Pending';
        } elseif ($log['status'] == 'Approved') {
            echo '<i class="fa-solid fa-check-circle"></i> Approved';
        } elseif ($log['status'] == 'Rejected') {
            echo '<i class="fa-solid fa-times-circle"></i> Rejected';
        } else {
            echo htmlspecialchars($log['status']);
        }
    ?>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5">No duty logs found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <p class="total-hours-container-text"><strong>Total Hours Worked:</strong>
            <?php 
                $total_seconds = $total_hours * 3600; // Convert hours to seconds

                $hours = floor($total_seconds / 3600); // Get full hours
                $minutes = round(($total_seconds % 3600) / 60); // Get remaining minutes

                if ($hours > 0) {
                    if ($minutes > 0) {
                        echo number_format($hours, 0) . " hr" . ($hours > 1 ? "s" : "") . " {$minutes} min";
                    } else {
                        echo number_format($hours, 0) . " hr" . ($hours > 1 ? "s" : "");
                    }
                } else {
                    // If there are no hours, display only minutes (if any)
                    echo $minutes > 0 ? "{$minutes} min" : "0 min";
                }
            ?>
            </p>

        </main>
    </div>

    <script>
    function filterByStatus(status) {
        let rows = document.querySelectorAll(".log-row");

        rows.forEach(row => {
            let rowStatus = row.getAttribute("data-status");

            if (status === "All" || rowStatus === status) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
    </script>

</body>

</html>