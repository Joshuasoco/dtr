<?php
session_start();
require_once '../config/database.php';
require 'hours_format.php';


if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}
$error = "";
$success = "";

$stmt = $pdo->prepare("
    SELECT dl.id, dl.student_id, dl.duty_date, dl.time_in, dl.time_out, dl.status, 
           s.name AS student_name,
           (SELECT IFNULL(SUM(hours_worked), 0) 
            FROM duty_logs 
            WHERE student_id = dl.student_id 
            AND status = 'Approved') AS total_hours_rendered
    FROM duty_logs dl
    INNER JOIN students s ON dl.student_id = s.student_id
    INNER JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE dl.status = 'Pending'
    AND sta.teacher_id = ?
    ORDER BY dl.duty_date DESC, dl.time_in ASC
");
$stmt->execute([$_SESSION['teacher_id']]);
$pending_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_id'], $_POST['action'])) {
    $log_id = $_POST['log_id'];
    $action = $_POST['action'];
    $teacher_id = $_SESSION['teacher_id'];

    // Check if there are updated values from the modal
    $updated_date = isset($_POST['editdate']) ? $_POST['editdate'] : null;
    $updated_time_in = isset($_POST['timein']) ? $_POST['timein'] : null;
    $updated_time_out = isset($_POST['timeout']) ? $_POST['timeout'] : null;

    $pdo->beginTransaction(); // Start transaction

    try {
        if ($action == 'Approved') {
            // Fetch duty log details
            $stmt_log = $pdo->prepare("SELECT time_in, time_out, student_id, duty_date FROM duty_logs WHERE id = ?");
            $stmt_log->execute([$log_id]);
            $log_data = $stmt_log->fetch(PDO::FETCH_ASSOC);

            if ($log_data) {
                // Use updated values if provided, otherwise use existing values
                $duty_date = $updated_date ?: $log_data['duty_date'];
                $time_in = $updated_time_in ?: $log_data['time_in'];
                $time_out = $updated_time_out ?: $log_data['time_out'];
                
                // Calculate hours worked
                $hours_worked = calculateHoursWorked($time_in, $time_out);

                // Update duty log with approved status and calculated hours worked
                $stmt_update = $pdo->prepare("
                    UPDATE duty_logs 
                    SET status = 'Approved', 
                        hours_worked = ?, 
                        teacher_id = ?, 
                        approved_at = NOW(),
                        duty_date = ?,
                        time_in = ?,
                        time_out = ?
                    WHERE id = ?
                ");
                $stmt_update->execute([$hours_worked, $teacher_id, $duty_date, $time_in, $time_out,  $log_id]);

                // Get latest total hours of student from duty_logs (only approved logs)
                $stmt_total_hours = $pdo->prepare("
                    SELECT IFNULL(SUM(hours_worked), 0) 
                    FROM duty_logs 
                    WHERE student_id = ? AND status = 'Approved'
                ");
                $stmt_total_hours->execute([$log_data['student_id']]);
                $total_hours_rendered = $stmt_total_hours->fetchColumn();

                // Update total hours in students table
                $stmt_student_update = $pdo->prepare("
                    UPDATE students 
                    SET total_hours = ? 
                    WHERE student_id = ?
                ");
                $stmt_student_update->execute([$total_hours_rendered, $log_data['student_id']]);

                // Update total hours in duty_logs for consistency
                $stmt_log_update = $pdo->prepare("
                    UPDATE duty_logs 
                    SET total_hours = ? 
                    WHERE student_id = ?
                ");
                $stmt_log_update->execute([$total_hours_rendered, $log_data['student_id']]);
            }
        } else {
            $stmt_reject = $pdo->prepare("
            UPDATE duty_logs 
            SET status = 'Rejected', 
                duration = NULL, 
                hours_worked = NULL, 
                total_hours = NULL 
            WHERE id = ?
        ");
        
            $stmt_reject->execute([$log_id]);
        }

        $pdo->commit(); // Commit transaction
        $success = "Duty log successfully updated.";
        header("Location: approve_duty.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error updating duty log: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Duty Logs</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
    <script src="../assets/search_filter.js"></script>
    <script src="../assets/delete_logs.js"></script>
    <script src="../assets/approve_logs.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/teacher_sidebar.php'?>

        <main class="main-content">
            <header class="header-container">
                <div class="header-left">
                    <h2> <i class="fa-solid fa-hourglass-half" style="color: #f39c12"></i> Pending Duty Logs</h2>
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

            <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
            <?php elseif (!empty($success)): ?>
            <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <section class="table-container">
                <div class="table-actions">
                    <button class="delete-btn" id="deleteSelected">
                        <i class="fa fa-trash"></i> Delete All
                    </button>

                    <button class="approve-selected-btn" id="approveSelected">
                        <i class="fa fa-thumbs-up"></i> Approve All
                    </button>
                </div>

                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Duty Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Hours Worked</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending_logs)): ?>
                            <tr>
                                <td colspan="10">No pending duty logs.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($pending_logs as $log): ?>
                            <tr>
                                <td><input type="checkbox" class="selectItem"
                                        value="<?php echo htmlspecialchars($log['id']); ?>"></td>
                                <td><?php echo htmlspecialchars($log['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($log['student_id']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($log['duty_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
                                <td><?php echo $log['time_out'] ? date('h:i A', strtotime($log['time_out'])) : 'N/A'; ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($log['status'] == 'Rejected') {
                                        echo "N/A"; // Display 'N/A' for rejected logs
                                    } else {
                                        formatDutyHours($log);
                                    }
                                    ?>
                                </td>

                                <td class="<?php echo $log['status'] == 'Pending' ? 'status-pending' : ''; ?>">
                                    <?php 
                                        if ($log['status'] == 'Pending') {
                                            echo '<i class="fa-solid fa-clock"></i> '; // Add the clock icon for Pending status
                                        } 
                                        echo htmlspecialchars($log['status']); 
                                    ?>
                                </td>
                                <td>
                                    <button type="button" onclick="openModal(
                                    <?php 
                                            echo htmlspecialchars(json_encode([
                                                'id' => $log['id'],
                                                'date' => date('Y-m-d', strtotime($log['duty_date'])),
                                                'timeIn' => date('H:i', strtotime($log['time_in'])),
                                                'timeOut' => $log['time_out'] ? date('H:i', strtotime($log['time_out'])) : '',
                                                'student' => $log['student_name'],
                                                'student_id' => $log['student_id']
                                            ])); 
                                        ?>)">
                                        <img src="../assets/image/threedots.svg" alt="actionbutton" class="three-dots">
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal from hk.php -->
    <div class="form-modal" id="form_popup">
        <div class="form-content">
            <h3>Review Time Log</h3>
            <form id="timeForm" method="POST" action="">
                <input type="hidden" id="log_id" name="log_id">

                <label for="edit_date">Date</label>
                <input type="date" id="edit_date" name="editdate" required>

                <label for="edit_timein">Time in</label>
                <input type="time" id="edit_timein" name="timein" required>

                <label for="edit_timeout">Time out</label>
                <input type="time" id="edit_timeout" name="timeout" required>

                <div class="buttons">
                    <button type="button" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="action" value="Rejected" class="reject-button">Reject</button>
                    <button type="submit" name="action" value="Approved" class="approve-button">Approve</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Function to open modal and pre-fill fields
    function openModal(logData) {
        document.getElementById('form_popup').style.display = 'flex';
        document.getElementById('log_id').value = logData.id;
        document.getElementById('edit_date').value = logData.date;
        document.getElementById('edit_timein').value = logData.timeIn;
        document.getElementById('edit_timeout').value = logData.timeOut;
        document.getElementById('student_info').textContent = 'Student: ' + logData.student + ' (' + logData
            .student_id + ')';
    }

    // Function to close modal
    function closeModal() {
        document.getElementById('form_popup').style.display = 'none';
    }

    // Close modal if user clicks outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('form_popup');
        if (event.target == modal) {
            closeModal();
        }
    }
    </script>

</body>

</html>