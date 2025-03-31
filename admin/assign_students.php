<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Handle form submission for assigning students
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_student'])) {
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;

    if ($student_id <= 0 || $teacher_id <= 0) {
        $error = "Invalid student or teacher selection.";
    } else {
        // Check if the student is already assigned to any teacher
        $check_stmt = $pdo->prepare("SELECT teacher_id FROM student_teacher_assignments WHERE student_id = ? AND status = 'Active'");
        $check_stmt->execute([$student_id]);
        $existing_assignment = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_assignment) {
            $_SESSION['error'] = "  ";
            header("Location: assign_students.php"); 
            exit();
        } else {
            // Insert new assignment
            $assign_stmt = $pdo->prepare("INSERT INTO student_teacher_assignments (student_id, teacher_id, assigned_by) VALUES (?, ?, ?)");
            if ($assign_stmt->execute([$student_id, $teacher_id, $_SESSION['admin_id']])) {
                $_SESSION['success'] = "Student successfully assigned to teacher!";
                header("Location: assign_students.php");
                exit();
            } else {
                $error = "Failed to assign student to teacher.";
            }
        }
    }
}


// Handle unassign action
if (isset($_GET['unassign']) && isset($_GET['id'])) {
    $assignment_id = intval($_GET['id']);
    
    $unassign_stmt = $pdo->prepare("DELETE FROM student_teacher_assignments WHERE id = ?");
    if ($unassign_stmt->execute([$assignment_id])) {
        $_SESSION['success'] = "Assignment deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete assignment.";
    }
    header("Location: assign_students.php");
    exit();
}

// Get list of teachers
try {
    $teacher_stmt = $pdo->prepare("SELECT id, name, department FROM teachers ORDER BY name");
    $teacher_stmt->execute();
    $teachers = $teacher_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading teachers: " . $e->getMessage();
    $teachers = [];
}

// Get list of students
try {
    $student_stmt = $pdo->prepare("SELECT id, student_id as student_number, name, department, course FROM students ORDER BY name");
    $student_stmt->execute();
    $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading students: " . $e->getMessage();
    $students = [];
}

// Get current assignments
try {
    $assignment_stmt = $pdo->prepare("
        SELECT 
            sta.id, 
            s.name AS student_name, 
            s.student_id AS student_number,
            s.department AS student_dept,
            t.name AS teacher_name,
            t.department AS teacher_dept,
            a.name AS assigned_by,
            sta.assignment_date
        FROM student_teacher_assignments sta
        JOIN students s ON sta.student_id = s.id
        JOIN teachers t ON sta.teacher_id = t.id
        JOIN admin a ON sta.assigned_by = a.id
        WHERE sta.status = 'Active'
        ORDER BY sta.assignment_date DESC
    ");
    $assignment_stmt->execute();
    $assignments = $assignment_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading assignments: " . $e->getMessage();
    $assignments = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Students to Teachers</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
    .container {
    width: 100%;
    max-width: 100%; /* Adjusted for a wider layout */
    margin: 0 auto;
    padding-top: 20px;
}

/* Assignment Form */
.assignment-form {
    width: 100%;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
}

/* Form Row */
.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 10px;
    border: 1px solid #aaa !important;
    border-radius: 4px;
}

/* Assign Button */
.btn-assign {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
}

/* Assignments Table */
.assignments-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Ensures columns are evenly spaced */
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.assignments-table th, 
.assignments-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    word-wrap: break-word; /* Prevents content overflow */
}

.assignments-table th {
    background-color: rgb(255, 255, 255);
    color: white;
}

/* Action Button */
.btn-unassign {
    background: #f44336;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

/* Messages */
.message {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
    text-align: center;
}

.error {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}
.btn-assign:hover {
    background: #45a049;
}

.btn-unassign:hover {
    background: #d32f2f;
}

    </style>
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
                    <h2><i class="fa-solid fa-user-plus"></i> Assign Students to Teachers</h2>
                </div>
            </header>

            <div class="container">
                <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="assignment-form">
                    <h2>New Assignment</h2>
                    <form action="" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="student_id">Select Student:</label>
                                <select name="student_id" id="student_id" required>
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['name'] . ' (' . $student['student_number'] . ') - ' . $student['department']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teacher_id">Select Teacher:</label>
                                <select name="teacher_id" id="teacher_id" required>
                                    <option value="">-- Select Teacher --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>">
                                        <?php echo htmlspecialchars($teacher['name'] . ' - ' . $teacher['department']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="assign_student" class="btn-assign">Assign Student to Teacher</button>
                    </form>
                </div>

                <h2>Current Assignments</h2>
                <?php if (empty($assignments)): ?>
                <p>No assignments found.</p>
                <?php else: ?>
                <table class="assignments-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Student Department</th>
                            <th>Teacher</th>
                            <th>Teacher Department</th>
                            <th>Assigned By</th>
                            <th>Date Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['student_dept']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['teacher_name']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['teacher_dept']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['assigned_by']); ?></td>
                            <td><?php echo htmlspecialchars(date('M d, Y g:i A', strtotime($assignment['assignment_date']))); ?></td>
                            <td>
                                <a href="?unassign=1&id=<?php echo $assignment['id']; ?>" 
                                   class="btn-unassign"
                                   onclick="return confirm('Are you sure you want to remove this assignment?')">
                                    Unassign
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>
