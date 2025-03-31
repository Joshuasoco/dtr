<?php
require_once '../config/database.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$stmt = $pdo->prepare("SELECT student_id, name FROM students WHERE name LIKE ? LIMIT 10");
$stmt->execute(["%$query%"]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];
foreach ($students as $student) {
    $results[] = [
        "id" => $student['student_id'],
        "text" => $student['name']
    ];
}

echo json_encode($results);
?>