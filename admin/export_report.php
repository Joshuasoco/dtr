<?php
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch students for dropdown
$stmt = $pdo->query("SELECT student_id, name FROM students ORDER BY name ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get student ID & status filter
$student_id = isset($_GET['student_id']) && !empty($_GET['student_id']) ? $_GET['student_id'] : null;




$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$duty_logs = []; // Empty array for logs
$total_hours = 0;
$student_name = "Select a student"; // Default name

if (!empty($student_id)) {
    // Fetch student name
    $stmt = $pdo->prepare("SELECT student_id, name, course, department, year_level, hk_duty_status FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        $student_id_display = $student['student_id'];  // Student ID
    $student_name = $student['name'];
    $student_course = $student['course'];
    $student_department = $student['department'];
    $student_year = $student['year_level'];
    $student_hk_status = $student['hk_duty_status'];;

        // Fetch duty logs using correct `VARCHAR` handling
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
            if (!empty($log['time_out']) && $log['status'] !== 'Rejected') { // Exclude rejected logs
                $time_in = strtotime($log['time_in']);
                $time_out = strtotime($log['time_out']);
                $hours = round(($time_out - $time_in) / 3600, 2);
                $total_hours += $hours;
            }
        }
    } else {
        $student_name = "Unknown";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Duty Logs Report</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <script src="../assets/search_filter.js"></script>
    <link rel="stylesheet" href="../assets/admin.css">
    <!-- Include jQuery & Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
    function submitForm() {
        let studentId = document.getElementById("studentSelect").value;
        let status = document.getElementById("statusFilter").value;

        if (studentId) {
            window.location.href = "export_report.php?student_id=" + encodeURIComponent(studentId) + "&status=" +
                encodeURIComponent(status);
        } else {
            alert("Please select a student.");
        }
    }



    function printReport() {
        let studentId = document.getElementById("studentSelect").value;
        let status = document.getElementById("statusFilter").value;
        if (studentId) {
            window.open("generate_pdf.php?student_id=" + studentId + "&status=" + status, "_blank");
        } else {
            alert("Please select a student first.");
        }
    }
    </script>
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="header-container">
                <h2><i class="fa-solid fa-file-export"></i>Export Duty Logs Report</h2>
            </header>

            <form method="GET">
                <div class="student-select-container">
                    <label for="studentSelect" class="student-select-label">Select Student:</label>
                    <select id="studentSelect" name="student_id" class="searchable-dropdown student-select-dropdown"
                        onchange="submitForm()">
                        <option value="">-- Choose a Student --</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?= htmlspecialchars($student['student_id'], ENT_QUOTES, 'UTF-8') ?>"
                            class="student-option" <?= ($student_id == $student['student_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" name="status" onchange="submitForm()">
                    <option value="all" <?= ($status_filter == 'all') ? 'selected' : '' ?>>All</option>
                    <option value="Approved" <?= ($status_filter == 'Approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= ($status_filter == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                </select>
            </form>

            <?php if ($student_id): ?>
            <div class="student-info-container">
                <p><strong>Student Name:</strong> <span
                        class="student-name"><?= htmlspecialchars($student_name) ?></span></p>
                <p><strong>Student ID:</strong> <span
                        class="student-id"><?= htmlspecialchars($student_id_display) ?></span></p>
                <p><strong>Course:</strong> <span class="student-course"><?= htmlspecialchars($student_course) ?></span>
                </p>
                <p><strong>Department:</strong> <span
                        class="student-department"><?= htmlspecialchars($student_department) ?></span></p>
                <p><strong>Year Level:</strong> <span class="student-year"><?= htmlspecialchars($student_year) ?></span>
                </p>
                <p><strong>HK Duty Status:</strong> <span
                        class="student-hk-status"><?= htmlspecialchars($student_hk_status) ?></span></p>
            </div>


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
            $total_seconds = $time_out - $time_in; // Calculate the total time in seconds

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
            <p class="total-hours-container"><strong>Total Hours Worked:</strong>
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

            <button class="btn-print-report" onclick="printReport()">Print Report</button>
            <?php endif; ?>
        </main>
    </div>
</body>
<script>
$(document).ready(function() {
    $('#studentSelect').select2({
        placeholder: "Search for a student...",
        allowClear: true,
        width: '100%'
    });
});
// Add placeholder to the Select2 search input field when dropdown opens
$('#studentSelect').on('select2:open', function() {
    $('.select2-search__field').attr('placeholder', 'Type a name or ID...');
});
</script>

</html>