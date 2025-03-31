<?php
require_once '../config/database.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$duty_logs = [];
$total_hours = 0;

if (!empty($student_id)) {
    $stmt = $pdo->prepare("SELECT name, course, department, year_level, hk_duty_status FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $student_name = $student['name'];
        $student_course = $student['course'];
        $student_department = $student['department'];
        $student_year = $student['year_level'];
        $student_hk_status = $student['hk_duty_status'];

        // Fetch duty logs
        $query = "SELECT * FROM duty_logs WHERE student_id = ?";
        $params = [$student_id];

        if ($status_filter === 'Approved' || $status_filter === 'Rejected') {
            $query .= " AND status = ?";
            $params[] = $status_filter;
        } else {
            $query .= " AND status IN ('Approved', 'Rejected')";
        }

        $query .= " ORDER BY duty_date DESC, time_in DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total hours
        foreach ($duty_logs as $log) {
            if (!empty($log['time_out']) && $log['status'] !== 'Rejected') {
                $time_in = strtotime($log['time_in']);
                $time_out = strtotime($log['time_out']);
                $hours = round(($time_out - $time_in) / 3600, 2);
                $total_hours += $hours;
            }
        }
    }
}

?>

<!-- Student Details -->
<p><strong>Student Name:</strong> <?= htmlspecialchars($student_name) ?></p>
<p><strong>Course:</strong> <?= htmlspecialchars($student_course) ?></p>
<p><strong>Department:</strong> <?= htmlspecialchars($student_department) ?></p>
<p><strong>Year Level:</strong> <?= htmlspecialchars($student_year) ?></p>
<p><strong>HK Duty Status:</strong> <?= htmlspecialchars($student_hk_status) ?></p>

<!-- Duty Logs Table -->
<table id="studentsTable" border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Duty Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours Worked</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($duty_logs)): ?>
        <?php foreach ($duty_logs as $log): ?>
        <tr>
            <td><?= date('Y-m-d', strtotime($log['duty_date'])) ?></td>
            <td><?= date('h:i A', strtotime($log['time_in'])) ?></td>
            <td><?= ($log['time_out']) ? date('h:i A', strtotime($log['time_out'])) : 'N/A' ?></td>
            <td>
                <?php 
                        if ($log['status'] === 'Rejected') {
                            echo "0 hrs"; 
                        } elseif ($log['time_out']) {
                            $time_in = strtotime($log['time_in']);
                            $time_out = strtotime($log['time_out']);
                            $hours = round(($time_out - $time_in) / 3600, 2);
                            echo number_format($hours, 2, '.', '') . " hrs"; 
                        } else {
                            echo "N/A"; 
                        }
                        ?>
            </td>
            <td><?= htmlspecialchars($log['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="5" style="text-align: center;">No duty logs found for this student.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Total Hours -->
<p><strong>Total Hours Worked:</strong> <?= number_format($total_hours, 2, '.', '') ?> hrs</p>