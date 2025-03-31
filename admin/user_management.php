<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$error = "";
$success = "";

// Full departments list
$departments = [
    "CEA" => "College of Engineering and Architecture",
    "CMA" => "College of Management and Accountancy", 
    "CAHS" => "College of Allied Health Sciences",
    "CITE" => "College of Information Technology Education",
    "CCJE" => "College of Criminal Justice Education", 
    "CELA" => "College of Education and Liberal Arts"
];

// Handle Add User form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    // Capture form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $department = trim($_POST['department']);
    $profilePicture = $_FILES['profilePicture'] ?? null;
    $role = "Teacher"; // Automatically set role to "teacher"

    // Validate form data
    if (empty($username) || empty($email) || empty($password) || empty($department)) {
        $error = "All required fields must be filled.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Handle profile picture upload
            $profilePicturePath = null;
            if ($profilePicture && $profilePicture['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename
                $fileExt = pathinfo($profilePicture['name'], PATHINFO_EXTENSION);
                $filename = uniqid('teacher_') . '.' . $fileExt;
                $targetPath = $uploadDir . $filename;

                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if (in_array($profilePicture['type'], $allowedTypes) && $profilePicture['size'] <= $maxSize) {
                    if (move_uploaded_file($profilePicture['tmp_name'], $targetPath)) {
                        $profilePicturePath = $filename;
                    } else {
                        $error = "Failed to upload profile picture.";
                    }
                } else {
                    $error = "Invalid profile picture. Only JPEG/PNG under 2MB allowed.";
                }
            }

            if (empty($error)) {
                try {
                    // Insert new teacher
                    $stmt = $pdo->prepare("INSERT INTO teachers (name, email, password, department, profile_photo, role) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$username, $email, $hashed_password, $department, $profilePicturePath, $role])) {
                        $success = "Teacher added successfully!";
                        
                        // Reset form by redirecting to avoid resubmission
                        header("Location: user_management.php");
                        exit();
                    } else {
                        $error = "Error occurred while adding the teacher.";
                    }
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch all teachers
try {
    $teacher_stmt = $pdo->prepare("SELECT * FROM teachers ORDER BY created_at DESC");
    $teacher_stmt->execute();
    $teachers = $teacher_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching teachers: " . $e->getMessage();
    $teachers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
            <main class="main-content">
                <header class="header-container">
                    <div class="header-left">
                        <div class="sidebar-toggle" id="menu-toggle">
                            <i class="fas fa-bars"></i>
                        </div>
                        <h2><i class="fa-solid fa-users-between-lines"></i>&nbsp;User Management</h2>
                    </div>

                    <div class="header-right">       
                        <button class="add-user-btn" id="openModalBtn">
                            Add user <i class="fa-solid fa-plus"></i>
                        </button>            
                    </div>
                </header>
                <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Teacher added successfully!</div>
                <?php endif; ?>
                <div class="users-grid" id="gridView">
                    <?php foreach ($teachers as $teacher): ?>
                    <div class="user-card" data-teacher-id="<?php echo htmlspecialchars($teacher['id']); ?>">
                        <div class="user-header">
                            <div class="user-name"><?php echo htmlspecialchars($teacher['name']); ?></div>
                            <p class="user-title">Teacher</p>
                        </div>
                        <div class="user-body">
                            <div class="user-info">
                                <p class="info-label">Email</p>
                                <p class="info-value"><?php echo htmlspecialchars($teacher['email']); ?></p>
                            </div>
                            <div class="user-info">
                                <p class="info-label">Department</p>
                                <p class="info-value"><?php echo htmlspecialchars($teacher['department']); ?></p>
                            </div>
                            <div class="user-info">
                                <p class="info-label">Created At</p>
                                <p class="info-value"><?php echo date('M j, Y g:i A', strtotime($teacher['created_at'])); ?></p>
                            </div>
                            <div class="user-actions">
                                <button class="action-btn edit-user" data-teacher-id="<?php echo htmlspecialchars($teacher['id']); ?>">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <button class="action-btn view-students" data-teacher-id="<?php echo htmlspecialchars($teacher['id']); ?>" 
                                        onclick="viewStudents(this)">
                                    <i class="fa-solid fa-users-rays"></i> View Students
                                </button>
                                <button class="action-btn delete-user" data-teacher-id="<?php echo htmlspecialchars($teacher['id']); ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
<!-- Delete Modal -->
<div class="delete-overlay" id="deleteModal">
    <div class="admin-modal" style="max-width: 400px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Confirm Deletion</h3>
            <button class="admin-modal-close" id="admin_close"><i class="fas fa-times"></i></button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer-delete">
            <button class="btn btn-cancel_modal" id="cancelDeleteBtn">Cancel</button>
            <button class="btn btn-delete_modal" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>
        
<!-- Add User Modal -->
<div class="admin-modal-overlay" id="userModal">
                <div class="admin-modal">
                    <div class="admin-header">
                        <h3 class="admin-title">Add New User</h3>
                        <button class="admin-close" id="adminClose_button" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="admin-body">
                        <form id="addForm" class="modal-form" method="POST" enctype="multipart/form-data">
                            <div class="form-grid">
                                <!-- Username -->
                                <div class="form-field">
                                    <label class="form-label required">Username</label>
                                    <input type="text" class="form-input" name="username" 
                                           placeholder="Enter username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                </div>

                                <!-- Email -->
                                <div class="form-field">
                                    <label class="form-label required">Email Address</label>
                                    <input type="email" class="form-input" name="email" 
                                           placeholder="user@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>

                                <!-- Password Fields -->
                                <div class="form-field">
                                    <label class="form-label required">Password</label>
                                    <div class="password-input">
                                        <input type="password" class="form-input" id="password" name="password" 
                                               placeholder="Create password" required minlength="8">
                                        <button type="button" class="password-toggle" aria-label="Show password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-meter"></div>
                                        <span class="strength-text">Password strength: <span>weak</span></span>
                                    </div>
                                    <ul class="password-hints">
                                        <li data-requirement="length">Minimum 8 characters</li>
                                        <li data-requirement="uppercase">At least one uppercase letter</li>
                                        <li data-requirement="number">At least one number</li>
                                        <li data-requirement="special">At least one special character</li>
                                    </ul>
                                </div>

                                <div class="form-field">
                                    <label class="form-label required">Confirm Password</label>
                                    <div class="password-input">
                                        <input type="password" class="form-input" id="confirmPassword" 
                                               placeholder="Re-enter password" required>
                                        <button type="button" class="password-toggle" aria-label="Show password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-match">
                                        <i class="fas fa-check-circle match-icon"></i>
                                        <span class="match-text">Passwords match</span>
                                    </div>
                                </div>

                                <!-- Department -->
                                <div class="form-field-group">
                                    <div class="form-field">
                                        <label class="form-label required">Select Department</label>
                                        <div class="select-wrapper">
                                            <select class="form-select" name="department" required>
                                                <option value="" disabled selected>Select Department</option>
                                                <?php foreach ($departments as $deptCode => $deptName): ?>
                                                    <option value="<?php echo htmlspecialchars($deptCode); ?>">
                                                        <?php echo htmlspecialchars($deptCode . " - " . $deptName); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="fas fa-chevron-down select-arrow"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Picture -->
                                <div class="form-field">
                                    <label class="form-label">Profile Photo</label>
                                    <div class="file-upload">
                                        <input type="file" class="form-input" name="profilePicture" 
                                               accept="image/png, image/jpeg">
                                        <div class="upload-preview">
                                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                            <span class="upload-text">Click to upload or drag and drop</span>
                                            <span class="upload-subtext">PNG, JPG (max. 2MB)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="admin-modal-footer">
                        <button class="btn-add btn-secondary" id="cancelBtn">Cancel</button>
                        <button class="btn-add btn-primary" id="submitBtn" type="submit" form="addForm">
                            Add User
                        </button>
                    </div>
            </div>
        </div>

<!-- Edit User Modal -->
<div class="admin-modal-overlay" id="editModal">
    <div class="admin-modal">
        <div class="admin-header">
            <h3 class="admin-title">Edit User</h3>
            <button class="admin-close" id="admin_close"><i class="fas fa-times"></i></button>
        </div>
        <div class="admin-body">
            <form id="editForm" class="modal-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editTeacherId" name="teacher_id">
                <div class="form-grid">
                    <!-- Username -->
                    <div class="form-field">
                        <label class="form-label required">Username</label>
                        <input type="text" class="form-input" name="username" required>
                    </div>

                    <!-- Email -->
                    <div class="form-field">
                        <label class="form-label required">Email Address</label>
                        <input type="email" class="form-input" name="email" required>
                    </div>

                    <!-- Department -->
                    <div class="form-field-group">
                        <div class="form-field">
                            <label class="form-label required">Select Department</label>
                            <div class="select-wrapper">
                                <select class="form-select" name="department" required>
                                    <option value="" disabled selected>Select Department</option>
                                    <?php foreach ($departments as $code => $name): ?>
                                        <option value="<?php echo htmlspecialchars($code); ?>">
                                            <?php echo htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-field">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-input" name="password" placeholder="Leave blank to keep current">
                    </div>

                    <!-- Profile Picture -->
                    <div class="form-field">
                        <label class="form-label">Profile Photo</label>
                        <div class="file-upload">
                            <input type="file" class="form-input" name="profilePicture" accept="image/png, image/jpeg">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="admin-modal-footer">
            <button class="btn-add btn-secondary" id="cancelEditBtn">Cancel</button>
            <button class="btn-add btn-primary" id="saveBtn">Save Changes</button>
        </div>
    </div>
</div>

    <script src="../assets/user_management.js" defer></script>
</body>
</html>