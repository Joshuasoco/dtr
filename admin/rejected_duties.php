<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = "";
$success = "";

// Fetch rejected logs
$stmt = $pdo->prepare("
    SELECT dl.id, dl.student_id, dl.duty_date, dl.time_in, dl.time_out, dl.status, 
           s.name AS student_name
    FROM duty_logs dl
    INNER JOIN students s ON dl.student_id = s.student_id
    WHERE dl.status = 'Rejected'
    ORDER BY dl.duty_date DESC, dl.time_in ASC
");
$stmt->execute();
$rejected_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate hours worked
function calculateHoursWorked($timeIn, $timeOut) {
    if (!$timeIn || !$timeOut) return 0;
    $time1 = new DateTime($timeIn);
    $time2 = new DateTime($timeOut);
    $interval = $time1->diff($time2);
    return round($interval->h + ($interval->i / 60), 2);
}

// Handle resubmission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_id'])) {
    $log_id = $_POST['log_id'];
    $updated_date = $_POST['editdate'];
    $updated_time_in = $_POST['timein'];
    $updated_time_out = $_POST['timeout'];
    $admin_id = $_SESSION['admin_id'];

    $hours_worked = calculateHoursWorked($updated_time_in, $updated_time_out);

    $pdo->beginTransaction(); // Start transaction

    try {
        // Update duty log for resubmission
        $stmt_update = $pdo->prepare("
            UPDATE duty_logs 
            SET status = 'Pending', 
                duty_date = ?, 
                time_in = ?, 
                time_out = ?, 
                hours_worked = NULL, 
                admin_id = ?, 
                approved_at = NULL
            WHERE id = ?
        ");
        $stmt_update->execute([$updated_date, $updated_time_in, $updated_time_out, $admin_id, $log_id]);

        $pdo->commit(); // Commit transaction
        $success = "Duty log successfully updated and resubmitted for approval.";
        header("Location: rejected_duties.php");
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
    <title>Rejected Duty Logs</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
    <script src="../assets/search_filter.js"></script>
    <script src="../assets/delete_logs.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php' ?>

        <main class="main-content">
            <header class="header-container">
                <div class="rejected-page">
                    <div class="header-left">
                        <h2><i class="fa-solid fa-thumbs-down"></i> Rejected Duty Logs</h2>
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
                </div>
                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th class="sortable" data-column="name">Student Name</th>
                                <th class="sortable" data-column="student_id">Student ID</th>
                                <th class="sortable" data-column="duty_date">Duty Date</th>
                                <th class="sortable" data-column="time_in">Time In</th>
                                <th class="sortable" data-column="time_out">Time Out</th>
                                <th class="sortable" data-column="hours_worked">Hours Worked</th>
                                <th class="sortable" data-column="status">Status</th>
                                <th class="sortable" data-column="action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rejected_logs)): ?>
                            <tr>
                                <td colspan="9">No rejected duty logs.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($rejected_logs as $log): ?>
                            <tr>
                                <td data-label="Select"><input type="checkbox" class="selectItem"
                                        value="<?php echo htmlspecialchars($log['id']); ?>"></td>
                                <td data-label="Student Name"><?php echo htmlspecialchars($log['student_name']); ?></td>
                                <td data-label="Student ID"><?php echo htmlspecialchars($log['student_id']); ?></td>
                                <td data-label="Duty Date"><?php echo date('Y-m-d', strtotime($log['duty_date'])); ?>
                                </td>
                                <td data-label="Time In"><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
                                <td data-label="Time Out">
                                    <?php echo $log['time_out'] ? date('h:i A', strtotime($log['time_out'])) : 'N/A'; ?>
                                </td>

                                <!-- <td data-label="Hours Worked">
                                    <?php echo calculateHoursWorked($log['time_in'], $log['time_out']); ?> hrs</td> -->

                                <td data-label="Hours Worked">
                                    <?php echo ($log['status'] === 'Rejected') ? '0' : htmlspecialchars($log['hours_worked']); ?>
                                    hrs
                                </td>

                                <td data-label="Status" class="status-rejected">
                                    <?php 
        if ($log['status'] == 'Rejected') {
            echo '<i class="fa-solid fa-times-circle"></i> '; // Add the rejected icon
        } 
        echo htmlspecialchars($log['status']); 
    ?>
                                </td>


                                <td data-label="Actions">
                                    <button type="button" onclick="openModal(<?php 
        echo htmlspecialchars(json_encode([
            'id' => $log['id'],
            'date' => date('Y-m-d', strtotime($log['duty_date'])),
            'timeIn' => date('H:i', strtotime($log['time_in'])),
            'timeOut' => $log['time_out'] ? date('H:i', strtotime($log['time_out'])) : '',
            'student' => $log['student_name'],
            'student_id' => $log['student_id']
        ])); 
    ?>)">
                                        <img src="../assets/image/threedots.svg" alt="action button" class="three-dots">
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

    <!-- Modal for Editing -->
    <div class="form-modal" id="form_popup">
        <div class="form-content">
            <h3>Edit & Resubmit Time Log</h3>
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
                    <button type="submit" class="approve-button">Resubmit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(logData) {
        document.getElementById('form_popup').style.display = 'flex';
        document.getElementById('log_id').value = logData.id;
        document.getElementById('edit_date').value = logData.date;
        document.getElementById('edit_timein').value = logData.timeIn;
        document.getElementById('edit_timeout').value = logData.timeOut;
    }

    function closeModal() {
        document.getElementById('form_popup').style.display = 'none';
    }
    </script>


</body>

</html>