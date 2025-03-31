<?php
session_start();
require_once '../config/database.php';


if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch approved duty logs
$stmt = $pdo->query("
    SELECT d.id, s.student_id, s.name, s.course, s.department, d.status, 
           d.duty_date, d.time_in, d.time_out, 
           d.hours_worked, d.total_hours, d.approved_at
    FROM duty_logs d
    JOIN students s ON d.student_id = s.student_id
    WHERE d.status = 'Approved'
    ORDER BY d.approved_at DESC
");
$approvedDuties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Duty Logs</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <script src="../assets/dashboard.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="../assets/search_filter.js"></script>
    <script src="../assets/delete_logs.js"></script>
    <script src="../assets/resubmit_logs.js"></script>
</head>

<body>
    <div class="dashboard-container">

        <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header-container">
                <div class="approved-page">
                    <div class="header-left">
                        <h2><i class="fas fa-check-square"></i> Approved Duty Logs</h2>
                    </div>
                </div>

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
            </header>
            <section class="table-container">
                <div class="table-actions">
                    <button class="resubmit-btn" id="resubmitSelected">
                        <i class="fa fa-refresh"></i> Resubmit
                    </button>
                    <button class="delete-btn" id="deleteSelected">
                        <i class="fa fa-trash"></i> Delete All
                    </button>
                </div>
                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th class="sortable" data-column="student_id">Student ID</th>
                                <th class="sortable" data-column="name">Name</th>
                                <th class="sortable" data-column="department">Department</th>
                                <th class="sortable" data-column="duty_date">Duty Date</th>
                                <th class="sortable" data-column="time_in">Time In</th>
                                <th class="sortable" data-column="time_out">Time Out</th>
                                <th class="sortable" data-column="hours_worked">Hours Worked</th>
                                <th class="sortable" data-column="status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedDuties as $log): ?>
                            <tr>
                                <td><input type="checkbox" class="selectItem"
                                        value="<?php echo htmlspecialchars($log['id']); ?>"></td>
                                <td data-label="Student ID"><?php echo htmlspecialchars($log['student_id']); ?></td>
                                <td data-label="Name"><?php echo htmlspecialchars($log['name']); ?></td>
                                <td data-label="Department"><?php echo htmlspecialchars($log['department']); ?></td>
                                <td data-label="Duty Date"><?php echo date('Y-m-d', strtotime($log['duty_date'])); ?>
                                </td>
                                <td data-label="Time In"><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
                                <td data-label="Time Out"><?php echo date('h:i A', strtotime($log['time_out'])); ?></td>
                                <td data-label="Hours Worked">
                                    <?php 
    $time_in = strtotime($log['time_in']);
    $time_out = strtotime($log['time_out']);

    if ($time_in && $time_out && $time_out > $time_in) {
        $total_seconds = $time_out - $time_in; // Calculate difference in seconds

        $hours = floor($total_seconds / 3600); // Get hours
        $minutes = round(($total_seconds % 3600) / 60); // Get remaining minutes

        if ($hours > 0) {
            echo "{$hours} hr" . ($hours > 1 ? "s" : "");
            if ($minutes > 0) {
                echo " {$minutes} min";
            }
        } else {
            echo "{$minutes} min"; // If less than 1 hour, show only minutes
        }
    } else {
        echo "0 min"; // If time_in or time_out is missing or invalid
    }
    ?>
                                </td>

                                </td>
                                <td data-label="Status" class="status-approved">
                                    <?php 
        if ($log['status'] == 'Approved') {
            echo '<i class="fa-solid fa-check-circle"></i> '; // Add the approved icon
        } 
        echo htmlspecialchars($log['status']); 
    ?>
                                </td>

                                <!-- <td data-label="Approved At"><?php echo date('Y-m-d h:i A', strtotime($log['approved_at'])); ?></td> -->
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            </section>
        </main>
    </div>

</body>
<script>

</script>

</html>