<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

$error = "";
$success = "";

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<pre>";
    echo "</pre>"; 
    // exit; // Uncomment to debug

    if (isset($_POST['email'])) {
        $student_id = trim($_POST['student_id']);
        $name = trim($_POST['name']);
        $scholarship_type = trim($_POST['scholarship_type']);
        $course = trim($_POST['course']);
        $department = trim($_POST['department']);
        $year_level = trim($_POST['year_level']);
        $hk_duty_status = trim($_POST['hk_duty_status']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (!empty($student_id) && !empty($name) && !empty($scholarship_type) && !empty($course) &&
            !empty($department) && !empty($year_level) && !empty($hk_duty_status) && !empty($email) &&
            !empty($password) && !empty($confirm_password)) {

            if ($password === $confirm_password) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Check if student_id or email already exists
                $stmt = $pdo->prepare("SELECT id FROM students WHERE student_id = ? OR email = ?");
                $stmt->execute([$student_id, $email]);

                if ($stmt->rowCount() == 0) {
                    // Insert student data
                    $stmt = $pdo->prepare("INSERT INTO students (student_id, name, scholarship_type, course, department, year_level, hk_duty_status, email, password) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$student_id, $name, $scholarship_type, $course, $department, $year_level, $hk_duty_status, $email, $hashed_password])) {
                        $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                    } else {
                        $error = "Error occurred during registration.";
                    }
                } else {
                    $error = "Student ID or Email already exists.";
                }
            } else {
                $error = "Passwords do not match.";
            }
        } else {
            $error = "Please fill in all fields.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="../assets/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
    function showStep(step) {
        document.getElementById('step1').style.display = (step === 1) ? 'block' : 'none';
        document.getElementById('step2').style.display = (step === 2) ? 'block' : 'none';
    }

    function nextStep() {
        // Copy values to hidden fields before submitting
        document.getElementById("hidden_student_id").value = document.getElementById("student_id").value;
        document.getElementById("hidden_email").value = document.getElementById("email").value;
        document.getElementById("hidden_name").value = document.getElementById("name").value;
        document.getElementById("hidden_password").value = document.getElementById("password").value;
        document.getElementById("hidden_confirm_password").value = document.getElementById("confirm_password").value;
        showStep(2);
    }
    </script>
</head>

<body onload="showStep(1)">

    <div class="container">
        <!-- Left Section (Image) -->
        <div class="left">
            <img src="../assets/image/Rectangle bg.png" alt="Registration Background">
        </div>

        <!-- Right Section (Form) -->
        <div class="right">
            <h2 class="reg_signup">Student Registration</h2>

            <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
            <?php elseif ($success): ?>
            <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="" method="POST" id="registrationForm">
                <!-- Step 1 -->
                <div id="step1">
                    <label for="student_id">Student ID:</label>
                    <input type="text" name="student_id" id="student_id" class="input" placeholder="Enter Student ID"
                        required>

                    <label for="name">Full Name:</label>
                    <input type="text" name="name" id="name" class="input" placeholder="Enter Full Name" required>

                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" class="input" placeholder="Enter Email" required>

                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" class="input" placeholder="Enter Password"
                        required>

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="input"
                        placeholder="Confirm Password" required>

                    <button type="button" class="btn" onclick="nextStep()">Next</button>
                </div>

                <!-- Step 2 -->
                <div id="step2" style="display: none;">
                    <input type="hidden" name="student_id" id="hidden_student_id">
                    <input type="hidden" name="name" id="hidden_name">
                    <input type="hidden" name="email" id="hidden_email">
                    <input type="hidden" name="password" id="hidden_password">
                    <input type="hidden" name="confirm_password" id="hidden_confirm_password">

                    <label for="hk_duty_status">HK Duty Status:</label>
                    <select name="hk_duty_status" class="input" required>
                        <option value="" disabled selected>Select Duty Status</option>
                        <option value="Module Distributor">Module Distributor</option>
                        <option value="Student Facilitator">Student Facilitator</option>
                        <option value="Library Assistant">Library Assistant</option>
                        <option value="Admin Assistant">External Facilitator</option>
                    </select>

                    <label for="scholarship_type">Scholarship Type:</label>
                    <select name="scholarship_type" class="input" required>
                        <option value="" disabled selected>Select Scholarship Type</option>
                        <option value="HK 25">HK 25</option>
                        <option value="HK 50">HK 50</option>
                        <option value="HK 75">HK 75</option>
                    </select>

                    <label for="course">Course:</label>
                    <select name="course" class="input" required>
                        <option value="" disabled selected>Select Course</option>
                        <option value="BSIT">BS Information Technology</option>
                        <option value="BSCS">BS Computer Science</option>
                        <option value="BSE">BS Education</option>
                        <option value="BBA">BS Business Administration</option>
                        <option value="BSCRIM">BS Criminology</option>
                        <option value="BSA">BS Accountancy</option>
                        <option value="BSE">BS Education</option>
                        <option value="BSN">BS Nursing</option>
                        <option value="BSARCH">BS Architecture</option>
                        <option value="BSCOE">BS Computer Engineering</option>
                        <option value="BSEE">BS Electrical Engineering</option>
                    </select>

                    <label for="department">Department:</label>
                    <select name="department" class="input" required>
                        <option value="" disabled selected>Select Department</option>
                        <option value="CEA">CEA</option>
                        <option value="CMA">CMA</option>
                        <option value="CAHS">CAHS</option>
                        <option value="CITE">CITE</option>
                        <option value="CCJE">CCJE</option>
                        <option value="CELA">CELA</option>
                    </select>

                    <label for="year_level">Year Level:</label>
                    <select name="year_level" class="input" required>
                        <option value="" disabled selected>Select Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="4th Year">5th Year</option>
                    </select>

                    <button type="submit" class="btn">Register</button>
                </div>
            </form>

            <p class="account_login">Already have an account? <a href="login.php">Login here</a></p>
            <span class="back-arrow" onclick="showStep(1)"><i class="fas fa-arrow-left"></i> Back</span>
        </div>
    </div>

</body>

</html>