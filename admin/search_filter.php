<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle search
$search = '';
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $stmt = $pdo->prepare("SELECT * FROM duty_logs WHERE student_id LIKE ? OR time_in LIKE ? OR time_out LIKE ?");
    $stmt->execute([$search, $search, $search]);
} else {
    // Default: Get all duty logs
    $stmt = $pdo->prepare("SELECT * FROM duty_logs");
    $stmt->execute();
}

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Duty Logs</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <div class="search-container">
        <h2>Search Duty Logs</h2>

        <form method="POST">
            <input type="text" name="search" placeholder="Search by Student ID, Time In, or Time Out"
                value="<?php echo $search; ?>" required>
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td><?php echo $log['student_id']; ?></td>
                    <td><?php echo $log['time_in']; ?></td>
                    <td><?php echo $log['time_out']; ?></td>
                    <td><?php echo $log['duration']; ?> hours</td>
                    <td><?php echo $log['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>