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
    $role = trim($_POST['role']);

    // Validate form data
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists in the 'admin' table
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email is already taken.";
        } else {
            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin into the 'admin' table
            $stmt = $pdo->prepare("INSERT INTO admin (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                $success = "Admin registered successfully!";
                header("Location: login.php"); // Redirect to login after registration
                exit();
            } else {
                $error = "Error occurred while registering the admin.";
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
    <title>Admin Registration</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <div class="register-container">
        <h2>Admin Registration</h2>

        <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
        <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <select name="role" required>
                <option value="Super Admin">Super Admin</option>
                <option value="Department Admin">Department Admin</option>
            </select>
            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>