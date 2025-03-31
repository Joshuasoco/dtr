<?php
session_start();
require_once '../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];

    if (empty($_POST['username'])) {
        // Fetch teacher details
        $stmt = $pdo->prepare("SELECT id, name, email, department, profile_photo FROM teachers WHERE id = ?");
        $stmt->execute([$teacher_id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($teacher) {
            echo json_encode(['success' => true, 'teacher' => $teacher]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Teacher not found']);
        }
        exit();
    } else {
        // Update teacher details
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $department = trim($_POST['department']);
        $password = !empty($_POST['password']) ? trim($_POST['password']) : null;
        $profilePicture = $_FILES['profilePicture'] ?? null;

        try {
            // Start transaction
            $pdo->beginTransaction();

            // Check if email exists for other teachers
            $stmt = $pdo->prepare("SELECT id FROM teachers WHERE email = ? AND id != ?");
            $stmt->execute([$email, $teacher_id]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('Email is already taken by another teacher');
            }

            // Handle profile picture upload if provided
            $profilePicturePath = null;
            if ($profilePicture && $profilePicture['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileExt = pathinfo($profilePicture['name'], PATHINFO_EXTENSION);
                $filename = uniqid('teacher_') . '.' . $fileExt;
                $targetPath = $uploadDir . $filename;

                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if (!in_array($profilePicture['type'], $allowedTypes) || $profilePicture['size'] > $maxSize) {
                    throw new Exception('Invalid profile picture. Only JPEG/PNG under 2MB allowed.');
                }

                // Get current profile picture to delete later
                $stmt = $pdo->prepare("SELECT profile_photo FROM teachers WHERE id = ?");
                $stmt->execute([$teacher_id]);
                $currentTeacher = $stmt->fetch();

                if (move_uploaded_file($profilePicture['tmp_name'], $targetPath)) {
                    $profilePicturePath = $filename;

                    // Delete old profile picture if exists
                    if ($currentTeacher && $currentTeacher['profile_photo']) {
                        $oldPicPath = $uploadDir . $currentTeacher['profile_photo'];
                        if (file_exists($oldPicPath)) {
                            unlink($oldPicPath);
                        }
                    }
                } else {
                    throw new Exception('Failed to upload profile picture');
                }
            }

            // Prepare update SQL
            $updateFields = ['name = ?', 'email = ?', 'department = ?'];
            $params = [$username, $email, $department];

            if ($password !== null) {
                $updateFields[] = 'password = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($profilePicturePath) {
                $updateFields[] = 'profile_photo = ?';
                $params[] = $profilePicturePath;
            }

            $params[] = $teacher_id;

            $sql = "UPDATE teachers SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Teacher updated successfully'
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or missing parameters'
    ]);
}