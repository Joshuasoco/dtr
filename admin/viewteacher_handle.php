<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get teacher_id from URL parameter
$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;
if (!$teacher_id) {
    header('Location: user_management.php');
    exit();
}

// Get teacher name
try {
    $teacher_stmt = $pdo->prepare("SELECT name FROM teachers WHERE id = ?");
    $teacher_stmt->execute([$teacher_id]);
    $teacher = $teacher_stmt->fetch(PDO::FETCH_ASSOC);
    $teacher_name = $teacher ? $teacher['name'] : 'Unknown Teacher';
} catch (PDOException $e) {
    $teacher_name = 'Unknown Teacher';
}

try {
    // Get assigned students with total duty hours in one query
    $student_stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.student_id AS student_number,
            s.name,
            s.email,
            s.department,
            s.course,
            s.scholarship_type,
            s.hk_duty_status,
            sta.assignment_date,
            COALESCE(SUM(TIMESTAMPDIFF(SECOND, dl.time_in, dl.time_out) / 3600), 0) AS total_hours
        FROM student_teacher_assignments sta
        JOIN students s ON sta.student_id = s.id
        LEFT JOIN duty_logs dl ON s.id = dl.student_id AND dl.status = 'Approved'
        WHERE sta.teacher_id = ? AND sta.status = 'Active'
        GROUP BY s.id
        ORDER BY s.name
    ");
    $student_stmt->execute([$teacher_id]);
    $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading students: " . $e->getMessage();
    $students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HK Duty Tracker</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
                    <h2>
                        <i class="fa-solid fa-arrow-left" onclick="window.location.href='user_management.php'" style="cursor: pointer;"></i>
                        &nbsp;Back to User Management
                    </h2>
                </div>
            </header>
            <div class="content-wrapper">
                <div class="welcome-section">
                    <h2>Teacher Name: <?php echo htmlspecialchars($teacher_name); ?></h2>
                    <p>Here you can view and manage the students assigned to you.</p>
                </div>
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-label">Assigned Students</div>
                            <div class="stat-number"><?php echo count($students); ?></div>
                        </div>
                    </div>
                </div>
                <div class="table-container">
                    <div class="table-header">
                        <h3>Student List</h3>
                        <div class="table-actions">
                            <div class="search-box">
                                <input type="text" id="searchInput" placeholder="Search students...">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Total Hours</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['department']); ?></td>
                                    <td><?php echo number_format($student['total_hours'], 1); ?> hours</td>
                                    <td class="actions">
                                        <a href="viewstudent_log.php?student_id=<?php echo htmlspecialchars($student['student_number']); ?>" class="view-detail-btn">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/script.js"></script>
</body>
</html>