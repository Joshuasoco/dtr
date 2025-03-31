<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture duty date, time in, and time out
    $duty_date = trim($_POST['duty_date']);
    $time_in = trim($_POST['time_in']);
    $time_out = trim($_POST['time_out']);
    $student_id = $_SESSION['student_id'];  // Get student ID from session

    // Combine duty date with time to create full datetime values
    $full_time_in = $duty_date . ' ' . $time_in;
    $full_time_out = $duty_date . ' ' . $time_out;

    // Calculate duration in hours
    $duration = (strtotime($full_time_out) - strtotime($full_time_in)) / 3600;  

    // Validate inputs
    if (empty($duty_date) || empty($time_in) || empty($time_out)) {
        $error = "Please provide the duty date, time in, and time out.";
    } elseif ($duration <= 0) {
        $error = "Time out must be later than time in.";
    } else {
        // Debugging: Check if student ID is set correctly
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $error = "Error: Student ID does not exist.";
        } else {
            // Insert duty log with correct student_id format
            $stmt = $pdo->prepare("INSERT INTO duty_logs (student_id, duty_date, time_in, time_out, duration, status) 
                                   VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmt->execute([$student_id, $duty_date, $full_time_in, $full_time_out, $duration, 'Pending'])) {
                $success = "Duty log added successfully!";
            } else {
                $error = "Error adding duty log: " . implode(" - ", $stmt->errorInfo());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Duty Log</title>
    <link rel="stylesheet" href="../assets/student.css">
</head>

<body>
    <div class="add-duty-container">
        <header>
            <h2>Add Duty Log</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </header>

        <!-- Display error or success messages -->
        <?php if (!empty($error)): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php elseif (!empty($success)): ?>
        <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Duty Log Form -->
        <form action="" method="POST">
            <label for="duty_date">Duty Date:</label>
            <input type="date" name="duty_date" required>

            <label for="time_in">Time In:</label>
            <input type="time" name="time_in" required>

            <label for="time_out">Time Out:</label>
            <input type="time" name="time_out" required>

            <button type="submit">Submit Duty Log</button>
        </form>
    </div>
</body>

</html>