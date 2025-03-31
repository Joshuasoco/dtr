<?php
require_once '../config/database.php'; 

header("Content-Type: application/json");

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $student_id = trim($data['student_id']);
    $password = trim($data['password']);

    if (!empty($student_id) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            $response["success"] = true;
            $response["message"] = "Login successful";
            $response["student"] = [
                "student_id" => $student["student_id"],
                "name" => $student["name"]
            ];
        } else {
            $response["message"] = "Invalid Student ID or Password.";
        }
    } else {
        $response["message"] = "Please fill in all fields.";
    }
}

echo json_encode($response);
?>