<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT s.id, s.student_id, s.name, s.email, s.course, s.scholarship_type 
    FROM students s
    INNER JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE sta.teacher_id = ?
");
$stmt->execute([$_SESSION['teacher_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT student_id, time_in, time_out, status 
    FROM duty_logs 
    WHERE status = 'Approved'
");
$stmt->execute();
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
    <title>Document</title>
</head>
<body>
    <div class="dashboard-container">

        <?php include '../includes/teacher_sidebar.php'?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header-container">
                <div class="header-left">

                    <h2>
                        <i class="fa-solid fa-arrow-left" onclick="window.location.href='dashboard.php'"
                            style="cursor: pointer;"></i>
                    </h2>

                    <h2><i class="fas fa-users"></i> Total Students</h2>
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
                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="student_id">
                                    Student ID</th>
                                <th class="sortable" data-column="name">Name</th>
                                <th class="sortable" data-column="course">Course</th>
                                <th class="sortable" data-column="scholartype">Scholarship Type</th>
                                <th class="sortable" data-column="email">Email </th>
                                <th class="sortable" data-column="duty_hours">Total Hours</th>
                                <th class="sortable" data-column="view_logs">View Logs</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td data-label="Student ID"><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td data-label="Name"><?php echo htmlspecialchars($student['name']); ?></td>
                            <td data-label="Course"><?php echo htmlspecialchars($student['course']); ?></td>
                            <td><?php echo htmlspecialchars($student['scholarship_type'] ?: 'N/A'); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($student['email']); ?></td>
                            <td>
                                <?php
                                // Get total duty hours for this student
                                $stmt = $pdo->prepare("SELECT time_in, time_out FROM duty_logs WHERE student_id = ? AND status = 'Approved'");
                                $stmt->execute([$student['student_id']]);
                                $duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $total_hours = 0;
                                foreach ($duty_logs as $log) {
                                    if (!empty($log['time_out'])) {
                                        $time_in = strtotime($log['time_in']);
                                        $time_out = strtotime($log['time_out']);
                                        $total_hours += round(($time_out - $time_in) / 3600, 2);
                                    }
                                }

                                // Convert hours to formatted display (e.g., "2 hrs 30 min")
                                $total_seconds = $total_hours * 3600;
                                $hours = floor($total_seconds / 3600);
                                $minutes = round(($total_seconds % 3600) / 60);

                                if ($hours > 0) {
                                    echo $hours . " hr" . ($hours > 1 ? "s" : "") . ($minutes > 0 ? " $minutes min" : "");
                                } else {
                                    echo $minutes > 0 ? "$minutes min" : "0 hrs";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="viewstudent_log.php?student_id=<?php echo htmlspecialchars($student['student_id']); ?>" class="view-logs-btn">
                                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" fill="blue">
                                        <path d="M8 2C4 2 1 6 1 6s3 4 7 4 7-4 7-4-3-4-7-4Zm0 6.5A2.5 2.5 0 1 1 8 3a2.5 2.5 0 0 1 0 5.5Z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>