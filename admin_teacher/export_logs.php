<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch students assigned to this teacher
$stmt = $pdo->prepare("
    SELECT s.student_id, s.name, s.course, s.department, s.year_level
    FROM students s
    JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE sta.teacher_id = ? AND sta.status = 'Active'
    ORDER BY s.name ASC
");
$stmt->execute([$teacher_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Duty Logs</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/teacher_sidebar.php'; ?>
        
        <main class="main-content">
            <header class="header-container">
                <h2><i class="fa-solid fa-file-export"></i> Export Duty Logs</h2>
            </header>

            <div class="content-container">
                <div class="filter-section">
                    <form action="generate_logs_pdf.php" method="get" target="_blank">
                        <div class="form-group">
                            <label for="student">Select Student:</label>
                            <select name="student_id" id="student" class="select2" required>
                                <option value="">Select a student...</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['student_id']) ?>">
                                    <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Status Filter:</label>
                            <select name="status" id="status">
                                <option value="all">All</option>
                                <option value="Approved">Approved Only</option>
                                <option value="Rejected">Rejected Only</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-print-report">
                            <i class="fas fa-file-pdf"></i>&nbsp; Print Report
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Search for a student...",
                allowClear: true
            });
        });
    </script>
</body>
</html>
