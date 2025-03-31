<?php
session_start();
require_once '../config/database.php';  // Include database connection
require_once '../config/session.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    if (!empty($student_id) && !empty($password)) {
        // Use the $pdo instance to query the database
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = trim($student['student_id']); // Store correctly formatted student ID
            $_SESSION['name'] = $student['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid Student ID or Password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="../assets/login.css">
</head>

<body>
    <div class="container">
        <div class="left">
            <img src="../assets/image/Rectangle bg.png" alt="Student Login">
        </div>
        <div class="right">
            <h2>Student Login</h2>

            <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <label for="student_id">Student ID:</label>
                <input type="text" name="student_id" required placeholder="Enter your student ID" required>

                <label for="password">Password:</label>
                <input type="password" name="password" required placeholder="Enter your password" required>

                <button type="submit" class="btn">Login</button>
            </form>

            <div class="divider">or</div>
            <a href="register.php" class="signup-btn">Register Here</a>

            <p class="terms">
                By logging in, you agree to our <a href="#">Terms & Conditions</a>.
            </p>
        </div>
    </div>
</body>

</html>