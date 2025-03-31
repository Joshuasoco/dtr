<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time_in = trim($_POST['time_in']);
    $time_out = trim($_POST['time_out']);
    $student_id = $_SESSION['student_id'];
    $duration = (strtotime($time_out) - strtotime($time_in)) / 3600;  // Calculate duration in hours

    if (empty($time_in) || empty($time_out)) {
        $error = "Please provide both time in and time out.";
    } elseif ($duration <= 0) {
        $error = "Time out must be later than time in.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO duty_logs (student_id, time_in, time_out, duration, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $time_in, $time_out, $duration, 'Pending'])) {
            $success = "Duty log added successfully!";
        } else {
            $error = "Error occurred while adding the duty log.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Duty Log</title>
    <link rel="stylesheet" href="../assets/student.css">
</head>

<body>
    <div class="process-duty-container">
        <header>
            <h2>Process Duty Log</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </header>

        <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
        <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form action="add_duty.php" method="POST">
            <label for="time_in">Time In:</label>
            <input type="datetime-local" name="time_in" value="<?php echo htmlspecialchars($time_in); ?>" required>

            <label for="time_out">Time Out:</label>
            <input type="datetime-local" name="time_out" value="<?php echo htmlspecialchars($time_out); ?>" required>

            <button type="submit">Submit Duty Log</button>
        </form>
    </div>
</body>

</html>