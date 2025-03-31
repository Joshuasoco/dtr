<?php
session_start();
require_once '../config/database.php';

$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $department = trim($_POST['department']);

    // Validate form data
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($department)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists in the 'teachers' table
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email is already taken.";
        } else {
            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new teacher into the 'teachers' table
            $stmt = $pdo->prepare("INSERT INTO teachers (name, email, password, department) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $department])) {
                $success = "Teacher registered successfully!";
                header("Location: login.php"); // Redirect to login after registration
                exit();
            } else {
                $error = "Error occurred while registering the teacher.";
            }
        }
    }
}

// Get department list for dropdown
try {
    $dept_stmt = $pdo->prepare("SELECT DISTINCT department FROM students");
    $dept_stmt->execute();
    $departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $departments = []; // Default empty array if query fails
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/teacher.css">
</head>

<body>
    <div class="register-container">
        <h2>Teacher Registration</h2>

        <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
        <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <select name="department" required>
                <option value="">Select Department</option>
                <option value="CEA">CEA</option>
                <option value="CMA">CMA</option>
                <option value="CAHS">CAHS</option>
                <option value="CITE">CITE</option>
                <option value="CCJE">CCJE</option>
                <option value="CELA">CELA</option>

                <?php foreach ($departments as $dept): ?>
                    <?php if (!in_array($dept, ["CEA", "CMA", "CAHS", "CITE", "CCJE", "CELA"])): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button type="submit">Register</button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>

</html>