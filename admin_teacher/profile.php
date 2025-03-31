<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch Profile Data
$sql = "SELECT profile_photo, name, email, department FROM teachers WHERE id = ?";
$stmt = $pdo->prepare($sql); 
$stmt->execute([$teacher_id]);
$teacher_data = $stmt->fetch(PDO::FETCH_ASSOC);

$profilePhoto = (!empty($teacher_data["profile_photo"])) ? "../uploads/" . htmlspecialchars($teacher_data["profile_photo"]) : "../uploads/default.jpg";
$teacher_name = htmlspecialchars($teacher_data['name'] ?? "Not Set");
$teacher_email = htmlspecialchars($teacher_data['email'] ?? "Email Not Set");
$teacher_department = htmlspecialchars($teacher_data['department'] ?? "Department Not Set");

// Fetch total students assigned to this teacher
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_students 
    FROM student_teacher_assignments sta
    JOIN students s ON sta.student_id = s.id
    WHERE sta.teacher_id = ? AND sta.status = 'Active'
");
$stmt->execute([$teacher_id]);
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'] ?? 0;

// Fetch pending duty logs for teacher's assigned students
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS pending_logs 
    FROM duty_logs dl 
    JOIN students s ON dl.student_id = s.student_id
    JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE sta.teacher_id = ? AND sta.status = 'Active' AND dl.status = 'Pending'
");
$stmt->execute([$teacher_id]);
$pending_logs = $stmt->fetch(PDO::FETCH_ASSOC)['pending_logs'];

// Debugging log
error_log("Teacher Department: " . $teacher_department);
error_log("Total Students: " . $total_students);
error_log("Pending Logs: " . $pending_logs);

// First name only
$first_name = explode(' ', $teacher_name)[0];

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HK DUTY TRACKER</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/image/icontitle.png">
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
    <style>
        /* Main profile modal styles */
        .profile-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .profile-modal {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        /* Profile header styles */
        .profile-modal-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .profile-header-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 1rem;
        }

        .profile-header-info h2 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .profile-header-info p {
            color: #6B7280;
            font-size: 0.875rem;
        }

        /* Profile photo styles */
        .profile-photo-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .photo-edit-button {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Form styles */
        .profile-form {
            width: 100%;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem; 
        }
        .form-group label {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563EB;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .full-width {
            width: 100%;
        }
        .divider {
            border: none;
            border-top: 1px solid #ddd;
            margin: 10px 0; 
        }
        .email-input-container {
            display: flex;
            align-items: center;
            position: relative;
        }

        .email-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
        }

        .email-input {
            padding-left: 2.5rem !important;
        }

        /* Verified badge styles */
        .verified-badge {
            display: inline-flex;
            align-items: center;
            background-color: #F3F4F6;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            color: #1F2937;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .verified-badge i {
            color: #2563EB;
            margin-right: 0.25rem;
        }

        /* Button styles */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .cancel-button {
            padding: 0.75rem 1rem;
            background-color: white;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .cancel-button:hover {
            background-color: #F9FAFB;
        }

        .save-button {
            padding: 0.75rem 1rem;
            background-color: #111827;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .save-button:hover {
            background-color: #374151;
        }

        /* Verification checkmark */
        .verification-checkmark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background-color: #2563EB;
            border-radius: 50%;
            color: white;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/teacher_sidebar.php' ?>
        <main class="main-content">
            <div class="content">
                <!-- Profile Section -->
                <div class="profile-container">
                    <div class="profile-card">
                        <img src="<?php echo $profilePhoto; ?>" alt="Profile Picture" class="profile-pic">
                        <div class="profile-info">
                            <h2 class="full-name"><?php echo $teacher_name; ?></h2>
                            <p class="role">
                                <i class="fa-solid fa-chalkboard-teacher"></i>
                                
                            </p>
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $total_students; ?></span>
                                    <span class="stat-label">Total Students</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $pending_logs; ?></span>
                                    <span class="stat-label">Pending Approval</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value" id="notificationCount">Unread Messages</span>
                                    <span class="stat-label">Notifications</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Details Section -->
                <div class="account-section">
                    <h2 class="section-title">Account Details</h2>
                    <div class="account-container">
                        <div class="account-card">
                            <div class="account-header">
                                <i class="fa-regular fa-user"></i>
                                <h3>Personal Information</h3>
                            </div>
                            <div class="account-details">
                                <div class="detail-item">
                                    <span class="detail-label">Account Status</span>
                                    <span class="status-active">Active</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Username:</span>
                                    <span class="detail-value"><?php echo $teacher_name; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value"><?php echo $teacher_email; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Department</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($_SESSION['teacher_dept']); ?></span>
                                </div>
                            </div>
                            <div class="edit-button">
                                <button><i class="fa-solid fa-pen-to-square"></i> Edit Information</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- New Profile Modal -->
    <div class="profile-modal-overlay">
        <div class="profile-modal">
            <div class="profile-modal-header">
                <div class="profile-photo-container">
                    <img id="profile-img" src="<?php echo $profilePhoto; ?>" alt="Profile Picture" class="profile-photo">
                    <input type="file" id="profile-photo-input" style="display: none;">
                    <div class="photo-edit-button" onclick="document.getElementById('profile-photo-input').click();">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>
                <div class="profile-header-info">
                    <h2><?php echo $teacher_name; ?></h2>
                    <p><?php echo $teacher_email; ?></p>
                </div>
            </div>
            <hr class="divider">
            <form class="profile-form" id="profileForm" method="POST" enctype="multipart/form-data">
                <div class="form-group full-width">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($teacher_name); ?>">
                </div>
                <hr class="divider">
                <div class="form-group full-width">
                    <label for="email">Email address</label>
                    <div class="email-input-container">
                        <i class="fa-solid fa-envelope email-icon"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher_email); ?>" class="email-input">
                    </div>
                </div>
                <hr class="divider">
                <div class="form-group full-width">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="">Select Department</option>
                        <option value="CEA" <?php echo ($teacher_department == "CEA") ? "selected" : ""; ?>>CEA</option>
                        <option value="CMA" <?php echo ($teacher_department == "CMA") ? "selected" : ""; ?>>CMA</option>
                        <option value="CAHS" <?php echo ($teacher_department == "CAHS") ? "selected" : ""; ?>>CAHS</option>
                        <option value="CITE" <?php echo ($teacher_department == "CITE") ? "selected" : ""; ?>>CITE</option>
                        <option value="CCJE" <?php echo ($teacher_department == "CCJE") ? "selected" : ""; ?>>CCJE</option>
                        <option value="CELA" <?php echo ($teacher_department == "CELA") ? "selected" : ""; ?>>CELA</option>
                        <?php 
                        foreach ($departments as $dept): 
                            if (!in_array($dept, ["CEA", "CMA", "CAHS", "CITE", "CCJE", "CELA"])): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>" 
                                    <?php echo ($teacher_department == $dept) ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <hr class="divider">
                <div class="form-actions">
                    <button type="button" class="cancel-button">Cancel</button>
                    <button type="submit" class="save-button">Save changes</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('profile-photo-input').addEventListener('change', function() {
    const fileInput = this;
    const formData = new FormData();
    formData.append('profile_photo', fileInput.files[0]);

    fetch('upload_teacher_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update the profile photo on the page
            document.getElementById('profile-img').src = data.filePath;
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the profile photo.');
    });
});
// Modal Handling
const editButtons = document.querySelectorAll('.edit-button'); // Supports multiple edit buttons
const modalOverlay = document.querySelector('.profile-modal-overlay');
const cancelButtons = document.querySelectorAll('.cancel-button');
const modal = document.querySelector('.profile-modal');

if (editButtons.length > 0) {
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            modalOverlay.style.display = 'flex';
        });
    });
}

// Hide modal when clicking overlay or cancel button
modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) {
        modalOverlay.style.display = 'none';
    }
});

// Close modal when clicking any cancel button
cancelButtons.forEach(button => {
    button.addEventListener('click', () => {
        modalOverlay.style.display = 'none';
    });
});

// Prevent modal close when clicking inside the modal
modal.addEventListener('click', (e) => {
    e.stopPropagation();
});

// Handle form submission
document.getElementById('profileForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(document.getElementById('profileForm')); // Automatically gathers all inputs

    fetch("update_profile.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            // Update UI with new values
            document.querySelector('.full-name').textContent = document.getElementById("username").value;

            const detailValues = document.querySelectorAll('.detail-value');
            if (detailValues.length > 1) {
                detailValues[0].textContent = document.getElementById("username").value; // First occurrence is username
                detailValues[1].textContent = document.getElementById("email").value; // Second occurrence is email
            }

            // Close modal
            modalOverlay.style.display = 'none';
            alert("Profile updated successfully!");
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while updating the profile.");
    });
});

    </script>
</body>

</html>