<?php
session_start();
require_once '../config/database.php';

// Check if teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// Get assigned students
try {
    $student_stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.student_id as student_number,
            s.name,
            s.email,
            s.department,
            s.course,
            s.scholarship_type,
            s.hk_duty_status,
            sta.assignment_date
        FROM student_teacher_assignments sta
        JOIN students s ON sta.student_id = s.id
        WHERE sta.teacher_id = ? AND sta.status = 'Active'
        ORDER BY s.name
    ");
    $student_stmt->execute([$teacher_id]);
    $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading students: " . $e->getMessage();
    $students = [];
}

// Count total duty hours for each student
$student_hours = [];
if (!empty($students)) {
    foreach ($students as $student) {
        try {
            $hours_stmt = $pdo->prepare("
                SELECT SUM(total_hours) as total_hours
                FROM duty_logs
                WHERE student_id = ? AND status = 'Approved'
            ");
            $hours_stmt->execute([$student['id']]);
            $hours_result = $hours_stmt->fetch(PDO::FETCH_ASSOC);
            $student_hours[$student['id']] = $hours_result['total_hours'] ?: 0;
        } catch (PDOException $e) {
            $student_hours[$student['id']] = 0;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/teacher.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .welcome-section {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .welcome-section h2 {
            margin-top: 0;
            color: #333;
        }
        
        .students-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .students-table th, .students-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .students-table th {
            background-color: #f2f2f2;
        }
        
        .duty-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-completed {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-in-progress {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .status-not-started {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #757575;
        }
        
        .stats-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }
        
        .stat-label {
            color: #757575;
            font-size: 14px;
        }
        
        .view-detail-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .nav-bar {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .logout-btn {
            padding: 8px 16px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="nav-bar">
            <h1>Teacher Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="welcome-section">
            <h2>Welcome, <?php echo htmlspecialchars($teacher_name); ?>!</h2>
            <p>Here you can view and manage your assigned students.</p>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-label">Assigned Students</div>
                <div class="stat-number"><?php echo count($students); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Department</div>
                <div class="stat-number"><?php echo htmlspecialchars($_SESSION['teacher_dept']); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Total Duty Hours Supervised</div>
                <div class="stat-number"><?php echo array_sum($student_hours); ?></div>
            </div>
        </div>
        
        <div class="students-section">
            <h2>My Assigned Students</h2>
            
            <?php if (empty($students)): ?>
            <div class="empty-state">
                <p>You don't have any assigned students yet.</p>
                <p>Students will be assigned to you by an administrator.</p>
            </div>
            <?php else: ?>
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Department</th>
                        <th>Scholarship Type</th>
                        <th>HK Duty Status</th>
                        <th>Total Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['course']); ?></td>
                        <td><?php echo htmlspecialchars($student['department']); ?></td>
                        <td><?php echo htmlspecialchars($student['scholarship_type'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="duty-status 
                                <?php 
                                if ($student['hk_duty_status'] == 'Completed') echo 'status-completed';
                                elseif ($student['hk_duty_status'] == 'In Progress') echo 'status-in-progress';
                                else echo 'status-not-started';
                                ?>">
                                <?php echo htmlspecialchars($student['hk_duty_status']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($student_hours[$student['id']], 2); ?> hrs</td>
                        <td>
                            <a href="student_detail.php?id=<?php echo $student['id']; ?>" class="view-detail-btn">
                                View Details
                            </a>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>