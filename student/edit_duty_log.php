<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Check if 'id' is provided in the URL
if (!isset($_GET['id'])) {
    echo "Duty Log ID not provided.";
    exit();
}

$duty_log_id = $_GET['id'];

// Fetch the existing duty log data
$stmt = $pdo->prepare("SELECT * FROM duty_logs WHERE id = ? AND student_id = ?");
$stmt->execute([$duty_log_id, $_SESSION['student_id']]);
$log = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the duty log exists
if (!$log) {
    echo "Duty log not found or you don't have permission to edit it.";
    exit();
}

// Handle form submission for updating the duty log
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time_in = trim($_POST['time_in']);
    $time_out = trim($_POST['time_out']);
    
    // Validate input
    if (empty($time_in) || empty($time_out)) {
        $error = "Both Time In and Time Out are required.";
    } else {
        // Update the duty log in the database
        $stmt = $pdo->prepare("UPDATE duty_logs SET time_in = ?, time_out = ?, status = 'Pending' WHERE id = ?");
        if ($stmt->execute([$time_in, $time_out, $duty_log_id])) {
            $success = "Duty log updated successfully!";
        } else {
            $error = "Error updating duty log.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Duty Log</title>
    <link rel="stylesheet" href="../assets/student.css">
</head>

<body>

    <div class="edit-duty-log-container">
        <header>
            <h2>Edit Duty Log</h2>
            <a href="duty_logs.php">Back to Duty Logs</a>
        </header>

        <!-- Display error or success messages -->
        <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
        <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Form to edit duty log -->
        <form method="POST">
            <label for="time_in">Time In:</label>
            <input type="datetime-local" name="time_in"
                value="<?php echo date('Y-m-d\TH:i', strtotime($log['time_in'])); ?>" required>

            <label for="time_out">Time Out:</label>
            <input type="datetime-local" name="time_out"
                value="<?php echo date('Y-m-d\TH:i', strtotime($log['time_out'])); ?>" required>

            <button type="submit">Update Duty Log</button>
        </form>
    </div>

</body>

</html>