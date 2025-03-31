<?php
require_once '../config/database.php'; 
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['ids'])) {
    $studentIds = implode(",", array_map("intval", $data['ids']));
    $query = "DELETE FROM students WHERE id IN ($studentIds)";

    $stmt = $pdo->prepare($query);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to execute query"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No IDs received"]);
}
?>