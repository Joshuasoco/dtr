<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch pending duties
$stmt = $pdo->query("
    SELECT d.*, s.name AS student_name, s.student_id, 
           TIMESTAMPDIFF(MINUTE, d.time_in, d.time_out) / 60 AS hours_worked
    FROM duty_logs d
    JOIN students s ON d.student_id = s.student_id
    WHERE d.status = 'Pending'
    ORDER BY d.duty_date DESC
");
$pending_duties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <script src="../assets/dashboard.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="../assets/search_filter.js"></script>
</head>

<body>
    <div class="dashboard-container">

        <?php include '../includes/sidebar.php'?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header-container">
                <div class="pending-page">
                    <div class="header-left">
                        <h2><i class="fa-solid fa-hourglass-half"></i> Pending Duties</h2>
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
                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="student_id">Student ID</th>
                                <th class="sortable" data-column="name">Name</th>
                                <th class="sortable" data-column="duty_date">Duty Date</th>
                                <th class="sortable" data-column="time_in">Time In</th>
                                <th class="sortable" data-column="time_out">Time Out</th>
                                <th class="sortable" data-column="hours_worked">Hours Worked</th>
                                <th class="sortable" data-column="status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_duties as $duty) : ?>
                            <tr>
                                <td data-label="Student ID"><?php echo htmlspecialchars($duty['student_id']); ?></td>
                                <td data-label="Name"><?php echo htmlspecialchars($duty['student_name']); ?></td>
                                <td data-label="Duty Date"><?php echo htmlspecialchars($duty['duty_date']); ?></td>
                                <td data-label="Time In">
                                    <?php echo date("h:i A", strtotime($duty['time_in'])); ?>
                                </td>
                                <td data-label="Time Out">
                                    <?php echo $duty['time_out'] ? date("h:i A", strtotime($duty['time_out'])) : 'N/A'; ?>
                                </td>

                                <td data-label="Hours Worked">
                                    <?php echo ($duty['time_out']) ? number_format($duty['hours_worked'], 2) . ' hrs' : 'N/A'; ?>
                                </td>
                                <td data-label="Status"><span class="statuspending">Pending</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            </section>
        </main>
    </div>


</body>

</html>