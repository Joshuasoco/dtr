<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize message variables
$message = [
    'type' => '',
    'text' => ''
];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $scholarship_type = trim($_POST['scholarship_type'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $hk_duty_status = trim($_POST['hk_duty_status'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? '')); // Ensure lowercase email
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate all required fields are filled
    $requiredFields = [
        $student_id, $name, $scholarship_type, $course, 
        $department, $year_level, $hk_duty_status, 
        $email, $password, $confirm_password
    ];

    if (in_array('', $requiredFields, true)) {
        $message = [
            'type' => 'error',
            'text' => 'Please fill in all fields.'
        ];
    } elseif ($password !== $confirm_password) {
        $message = [
            'type' => 'error',
            'text' => 'Passwords do not match.'
        ];
    } else {
        try {
            // Check if student_id or email already exists separately
            $stmt = $pdo->prepare("SELECT student_id, email FROM students WHERE student_id = ? OR email = ?");
            $stmt->execute([$student_id, $email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch a row

            if ($existing) {
                if ($existing['student_id'] == $student_id) {
                    $message = [
                        'type' => 'error',
                        'text' => 'Student ID already exists.'
                    ];
                } elseif ($existing['email'] == $email) {
                    $message = [
                        'type' => 'error',
                        'text' => 'Email already exists.'
                    ];
                }
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                // Insert student data
                $stmt = $pdo->prepare("INSERT INTO students (student_id, name, scholarship_type, course, department, year_level, hk_duty_status, email, password) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                if ($stmt->execute([$student_id, $name, $scholarship_type, $course, $department, $year_level, $hk_duty_status, $email, $hashed_password])) {
                    $message = [
                        'type' => 'success',
                        'text' => 'Student successfully registered!'
                    ];
                    
                    // Clear POST data after successful submission
                    $_POST = [];
                } else {
                    $message = [
                        'type' => 'error',
                        'text' => 'Error occurred while adding student.'
                    ];
                }
            }
        } catch (PDOException $e) {
            $message = [
                'type' => 'error',
                'text' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'?>

        <main class="main-content">
            <div class="form-container">
                <h2><i class="fas fa-user-graduate"></i> Student Registration</h2>

                <form action="" method="POST" id="studentRegistrationForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="student_id"><i class="fas fa-id-card"></i> Student ID:</label>
                            <input type="text" name="student_id" id="student_id" placeholder="Enter student ID"
                                value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Full Name:</label>
                            <input type="text" name="name" id="name" placeholder="Enter full name"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                            <input type="email" name="email" id="email" placeholder="Enter email address"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="scholarship_type"><i class="fas fa-award"></i> Scholarship Type:</label>
                            <select name="scholarship_type" id="scholarship_type" required>
                                <option value="" disabled selected>Select Scholarship Type</option>
                                <option value="HK 25"
                                    <?= isset($_POST['scholarship_type']) && $_POST['scholarship_type'] == 'HK 25' ? 'selected' : '' ?>>
                                    HK 25</option>
                                <option value="HK 50"
                                    <?= isset($_POST['scholarship_type']) && $_POST['scholarship_type'] == 'HK 50' ? 'selected' : '' ?>>
                                    HK 50</option>
                                <option value="HK 75"
                                    <?= isset($_POST['scholarship_type']) && $_POST['scholarship_type'] == 'HK 75' ? 'selected' : '' ?>>
                                    HK 75</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="course"><i class="fas fa-graduation-cap"></i> Course:</label>
                            <select name="course" id="course" required>
                                <option value="" disabled selected>Select Course</option>
                                <option value="BSIT">BS Information Technology</option>
                                <option value="BSCS">BS Computer Science</option>
                                <option value="BSE">BS Education</option>
                                <option value="BBA">BS Business Administration</option>
                                <option value="BSCRIM">BS Criminology</option>
                                <option value="BSA">BS Accountancy</option>
                                <option value="BSN">BS Nursing</option>
                                <option value="BSARCH">BS Architecture</option>
                                <option value="BSCOE">BS Computer Engineering</option>
                                <option value="BSEE">BS Electrical Engineering</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department"><i class="fas fa-building"></i> Department:</label>
                            <select name="department" id="department" required>
                                <option value="" disabled selected>Select Department</option>
                                <option value="CEA">CEA</option>
                                <option value="CMA">CMA</option>
                                <option value="CAHS">CAHS</option>
                                <option value="CITE">CITE</option>
                                <option value="CCJE">CCJE</option>
                                <option value="CELA">CELA</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="year_level"><i class="fas fa-layer-group"></i> Year Level:</label>
                            <select name="year_level" id="year_level" required>
                                <option value="" disabled selected>Select Year Level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                                <option value="5th Year">5th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hk_duty_status"><i class="fas fa-tasks"></i> HK Duty Status:</label>
                            <select name="hk_duty_status" id="hk_duty_status" required>
                                <option value="" disabled selected>Select Duty Status</option>
                                <option value="Module Distributor">Module Distributor</option>
                                <option value="Student Facilitator">Student Facilitator</option>
                                <option value="Library Assistant">Library Assistant</option>
                                <option value="Admin Assistant">External Facilitator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password:</label>
                            <input type="password" name="password" id="password" placeholder="Enter password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm Password:</label>
                            <input type="password" name="confirm_password" id="confirm_password"
                                placeholder="Confirm password" required>
                        </div>
                    </div>

                    <button type="submit"><i class="fas fa-user-plus"></i> Register Student</button>
                </form>
            </div>
        </main>
    </div>
    <div id="toast-container"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('studentRegistrationForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        // Client-side password validation
        form.addEventListener('submit', function(event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                showToast("Passwords do not match!", "error");
                return;
            }
        });

        // Server-side message handling
        <?php if (!empty($message['text'])): ?>
        showToast("<?= addslashes($message['text']) ?>", "<?= $message['type'] ?>");
        <?php endif; ?>
    });

    function showToast(message, type) {
        const toastContainer = document.getElementById("toast-container");

        // Remove any existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());

        const toast = document.createElement("div");
        toast.classList.add("toast", type);
        toast.innerHTML = `
            <span class="icon">${type === "success" ? "✔" : "✖"}</span>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <div class="toast-progress"></div>
        `;

        toastContainer.appendChild(toast);
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 5000);
    }
    </script>
</body>

</html>