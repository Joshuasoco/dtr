<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("SELECT id, student_id, name, email FROM students");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="../assets/search_filter.js"></script>
    <script src="../assets/remove_students.js"></script>
</head>

<body>
    <div class="dashboard-container">

        <?php include '../includes/sidebar.php'?>

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
                <div class="table-actions">
                    <button class="delete-btn" id="deleteSelected">
                        <i class="fas fa-user-times"></i> Remove User
                    </button>
                    <a href="add_students.php" class="add-student-btn">
                        <i class="fa fa-user-plus"></i> Add Student
                    </a>
                </div>

                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th class="sortable" data-column="id">ID</th>
                                <th class="sortable" data-column="student_id">
                                    Student ID</th>
                                <th class="sortable" data-column="name">Name </th>
                                <th class="sortable" data-column="email">Email </th>
                                <th class="sortable" data-column="view_logs">View Logs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><input type="checkbox" class="selectItem"
                                        value="<?php echo htmlspecialchars($student['id']); ?>"></td>

                                <td data-label="ID"><?php echo htmlspecialchars($student['id']); ?></td>
                                <td data-label="Student ID"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td data-label="Name"><?php echo htmlspecialchars($student['name']); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <a href="viewstudent_log.php?student_id=<?php echo htmlspecialchars($student['student_id']); ?>"
                                        class="view-logs-btn">

                                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 16 16" fill="blue">
                                            <path
                                                d="M8 2C4 2 1 6 1 6s3 4 7 4 7-4 7-4-3-4-7-4Zm0 6.5A2.5 2.5 0 1 1 8 3a2.5 2.5 0 0 1 0 5.5Z" />
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